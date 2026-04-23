"""
Asentinel Webhook — Async Monitoring Scheduler

Background task that periodically checks configured HTTP endpoints
and pushes status changes through the same webhook pipeline used by
the Laravel app (cooldown → Telegram).
"""

import asyncio
import json
import os
from datetime import datetime, timezone
from typing import Any, Dict, List

import httpx

from utils.logger import logger
from utils.cooldown import cooldown_manager
from services.telegram import send_alert


# ─── Configuration ───────────────────────────────────────────

MONITOR_ENABLED: bool = os.getenv("MONITOR_ENABLED", "true").lower() == "true"
_DEFAULT_INTERVAL = 60  # seconds
_HTTP_TIMEOUT = 10.0    # seconds


def _load_targets() -> List[Dict[str, Any]]:
    """
    Parse MONITOR_TARGETS from environment.

    Expected format (JSON array):
    [
      {"name": "Laravel-App", "url": "http://localhost:8000/health", "interval": 60},
      {"name": "Frontend", "url": "https://example.com", "interval": 120}
    ]
    """
    raw = os.getenv("MONITOR_TARGETS", "[]")
    try:
        targets = json.loads(raw)
        if not isinstance(targets, list):
            logger.error("MONITOR_TARGETS must be a JSON array, got %s", type(targets))
            return []
        logger.info("Loaded %d monitor target(s)", len(targets))
        return targets
    except json.JSONDecodeError as exc:
        logger.error("Failed to parse MONITOR_TARGETS: %s", exc)
        return []


# ─── Per-service state (track previous status for change detection) ─
_previous_status: Dict[str, str] = {}


# ─── Core Check Logic ───────────────────────────────────────

async def _check_single_target(target: Dict[str, Any]) -> None:
    """
    Perform an HTTP GET health check on a single target.

    A service is considered DOWN if:
    - Connection fails / times out
    - Status code is not in the 2xx range
    """
    name: str = target.get("name", "Unknown")
    url: str = target.get("url", "")

    if not url:
        logger.warning("Monitor target '%s' has no URL — skipping", name)
        return

    status = "UP"
    message = "OK"

    try:
        async with httpx.AsyncClient(timeout=_HTTP_TIMEOUT) as client:
            response = await client.get(url)

        if 200 <= response.status_code < 300:
            status = "UP"
            message = f"HTTP {response.status_code}"
            logger.debug("✓ %s — %s (%s)", name, url, message)
        else:
            status = "DOWN"
            message = f"HTTP {response.status_code}"
            logger.warning("✗ %s — %s returned %s", name, url, message)

    except httpx.TimeoutException:
        status = "DOWN"
        message = "Connection Timeout"
        logger.warning("✗ %s — %s timed out", name, url)

    except httpx.ConnectError:
        status = "DOWN"
        message = "Connection Refused"
        logger.warning("✗ %s — %s connection refused", name, url)

    except Exception as exc:
        status = "DOWN"
        message = str(exc)[:200]
        logger.warning("✗ %s — %s error: %s", name, url, message)

    # ─── Status change detection & notification ──────────
    prev = _previous_status.get(name)
    _previous_status[name] = status

    # Only notify on actual status changes (or first check that is DOWN)
    should_notify = False
    if prev is None and status == "DOWN":
        should_notify = True
    elif prev is not None and prev != status:
        should_notify = True

    if should_notify:
        timestamp = datetime.now(timezone.utc).isoformat()
        if cooldown_manager.can_send(name, status):
            await send_alert(
                service_name=name,
                status=status,
                message=message,
                timestamp=timestamp,
            )
        else:
            logger.debug("Alert suppressed by cooldown: %s [%s]", name, status)


# ─── Scheduler Loop ─────────────────────────────────────────

async def _monitor_loop(target: Dict[str, Any]) -> None:
    """
    Continuously check a single target at its configured interval.
    Each target runs in its own coroutine so they don't block each other.
    """
    name = target.get("name", "Unknown")
    interval = int(target.get("interval", _DEFAULT_INTERVAL))
    logger.info("Monitor started: %s (every %ds)", name, interval)

    while True:
        try:
            await _check_single_target(target)
        except Exception as exc:
            logger.error("Unhandled error checking %s: %s", name, exc)
        await asyncio.sleep(interval)


async def start_monitoring() -> List[asyncio.Task]:
    """
    Launch background monitor tasks for all configured targets.

    Returns the list of asyncio Tasks so they can be cancelled on shutdown.
    """
    if not MONITOR_ENABLED:
        logger.info("Monitoring scheduler is DISABLED (MONITOR_ENABLED=false)")
        return []

    targets = _load_targets()
    if not targets:
        logger.info("No monitor targets configured — scheduler idle")
        return []

    tasks = []
    for target in targets:
        task = asyncio.create_task(
            _monitor_loop(target),
            name=f"monitor-{target.get('name', 'unknown')}",
        )
        tasks.append(task)

    logger.info("Monitoring scheduler started with %d target(s)", len(tasks))
    return tasks


async def stop_monitoring(tasks: List[asyncio.Task]) -> None:
    """Cancel all running monitor tasks gracefully."""
    for task in tasks:
        task.cancel()
        try:
            await task
        except asyncio.CancelledError:
            pass
    logger.info("Monitoring scheduler stopped")


def get_monitor_status() -> Dict[str, Any]:
    """Return current known status of all monitored targets."""
    return {
        "enabled": MONITOR_ENABLED,
        "targets": _load_targets(),
        "current_status": dict(_previous_status),
    }
