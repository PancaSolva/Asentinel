"""
Asentinel Webhook — FastAPI Application

Main entry point.  Provides:
  POST /webhook          — Receive monitoring payloads (from Laravel or custom)
  POST /webhook/test     — Send a test notification
  GET  /health           — Health check
  GET  /status           — Current monitor & cooldown state
"""

import os
import sys
from contextlib import asynccontextmanager
from datetime import datetime, timezone
from typing import List, Literal

import asyncio
from fastapi import FastAPI, Header, HTTPException, Request
from fastapi.responses import JSONResponse
from pydantic import BaseModel, Field

# ─── Ensure project root is on sys.path for imports ─────────
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from dotenv import load_dotenv

load_dotenv()  # Load .env before any module reads os.getenv

from utils.logger import logger
from utils.cooldown import cooldown_manager
from services.telegram import send_alert
from services.monitor import start_monitoring, stop_monitoring, get_monitor_status

# ─── Configuration ───────────────────────────────────────────
WEBHOOK_SECRET: str = os.getenv("WEBHOOK_SECRET", "")
APP_VERSION = "1.0.0"

# ─── Pydantic Models ────────────────────────────────────────


class WebhookPayload(BaseModel):
    """Payload schema for incoming webhook events."""

    service_name: str = Field(..., min_length=1, description="Name of the service")
    status: Literal["UP", "DOWN", "up", "down"] = Field(
        ..., description="Service status"
    )
    message: str = Field(default="", description="Event description / error message")
    timestamp: str = Field(
        default="", description="ISO-8601 timestamp (auto-filled if empty)"
    )


class WebhookResponse(BaseModel):
    """Standard webhook response."""

    success: bool
    message: str
    data: dict | None = None


# ─── Lifespan (startup / shutdown) ──────────────────────────

_monitor_tasks: List[asyncio.Task] = []


@asynccontextmanager
async def lifespan(app: FastAPI):
    """Manage monitoring scheduler lifecycle."""
    global _monitor_tasks
    logger.info("🚀 Asentinel Webhook v%s starting…", APP_VERSION)
    _monitor_tasks = await start_monitoring()
    yield
    logger.info("🛑 Shutting down…")
    await stop_monitoring(_monitor_tasks)


# ─── FastAPI App ─────────────────────────────────────────────

app = FastAPI(
    title="Asentinel Webhook",
    description="Webhook receiver & monitoring notifier for Asentinel infrastructure.",
    version=APP_VERSION,
    lifespan=lifespan,
)


# ─── Helpers ─────────────────────────────────────────────────


def _validate_secret(provided: str | None) -> None:
    """Raise 401 if a secret is configured but the request doesn't match."""
    if WEBHOOK_SECRET and provided != WEBHOOK_SECRET:
        logger.warning("Unauthorized webhook attempt (bad secret)")
        raise HTTPException(status_code=401, detail="Invalid or missing webhook secret")


def _normalise_timestamp(raw: str) -> str:
    """Return an ISO timestamp, defaulting to now(UTC) if empty."""
    if raw:
        return raw
    return datetime.now(timezone.utc).isoformat()


# ─── Routes ──────────────────────────────────────────────────


@app.post("/webhook", response_model=WebhookResponse)
async def receive_webhook(
    payload: WebhookPayload,
    x_webhook_secret: str | None = Header(default=None),
):
    """
    Receive a monitoring event and optionally forward to Telegram.

    The request is validated, checked against cooldown rules, and — if allowed —
    dispatched to the Telegram notifier.
    """
    _validate_secret(x_webhook_secret)

    status_upper = payload.status.upper()
    timestamp = _normalise_timestamp(payload.timestamp)

    logger.info(
        "Webhook received: service=%s status=%s message=%s",
        payload.service_name,
        status_upper,
        payload.message,
    )

    # ── Cooldown gate ────────────────────────────────────
    if not cooldown_manager.can_send(payload.service_name, status_upper):
        logger.info(
            "Alert suppressed by cooldown: %s [%s]", payload.service_name, status_upper
        )
        return WebhookResponse(
            success=True,
            message="Received but alert suppressed (cooldown active)",
            data={
                "service_name": payload.service_name,
                "status": status_upper,
                "alert_sent": False,
            },
        )

    # ── Send Telegram notification ───────────────────────
    sent = await send_alert(
        service_name=payload.service_name,
        status=status_upper,
        message=payload.message,
        timestamp=timestamp,
    )

    return WebhookResponse(
        success=True,
        message="Webhook processed",
        data={
            "service_name": payload.service_name,
            "status": status_upper,
            "alert_sent": sent,
        },
    )


@app.post("/webhook/test", response_model=WebhookResponse)
async def test_webhook(
    x_webhook_secret: str | None = Header(default=None),
):
    """Send a test DOWN + UP notification pair to verify Telegram integration."""
    _validate_secret(x_webhook_secret)

    logger.info("Test notification triggered")
    timestamp = datetime.now(timezone.utc).isoformat()

    sent = await send_alert(
        service_name="Test-Service",
        status="DOWN",
        message="This is a TEST alert from Asentinel Webhook",
        timestamp=timestamp,
    )

    return WebhookResponse(
        success=True,
        message="Test notification sent" if sent else "Test notification failed",
        data={"alert_sent": sent},
    )


@app.get("/health")
async def health_check():
    """Simple liveness probe."""
    return {
        "status": "healthy",
        "version": APP_VERSION,
        "timestamp": datetime.now(timezone.utc).isoformat(),
    }


@app.get("/status")
async def system_status(
    x_webhook_secret: str | None = Header(default=None),
):
    """Return detailed system status including monitor & cooldown state."""
    _validate_secret(x_webhook_secret)

    return {
        "version": APP_VERSION,
        "monitoring": get_monitor_status(),
        "cooldown": cooldown_manager.get_status(),
        "timestamp": datetime.now(timezone.utc).isoformat(),
    }


# ─── Global Exception Handler ───────────────────────────────


@app.exception_handler(Exception)
async def global_exception_handler(request: Request, exc: Exception):
    """Catch-all so unhandled errors never leak stack traces to clients."""
    logger.error("Unhandled exception on %s: %s", request.url.path, exc, exc_info=True)
    return JSONResponse(
        status_code=500,
        content={"success": False, "message": "Internal server error"},
    )


# ─── CLI Entry Point ────────────────────────────────────────

if __name__ == "__main__":
    import uvicorn

    host = os.getenv("HOST", "0.0.0.0")
    port = int(os.getenv("PORT", "9000"))
    logger.info("Starting server on %s:%d", host, port)
    uvicorn.run(
        "main:app",
        host=host,
        port=port,
        reload=bool(os.getenv("APP_DEBUG", "false").lower() == "true"),
        log_level=os.getenv("LOG_LEVEL", "info").lower(),
    )
