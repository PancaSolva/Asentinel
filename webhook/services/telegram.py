"""
Asentinel Webhook — Telegram Notification Service

Sends formatted Markdown alerts to a Telegram chat/group via the Bot API.
Includes exponential-backoff retry logic (max 3 attempts).
"""

import asyncio
import os
from datetime import datetime

import httpx

from utils.logger import logger

# ─── Configuration (from environment) ────────────────────────
TELEGRAM_BOT_TOKEN: str = os.getenv("TELEGRAM_BOT_TOKEN", "")
TELEGRAM_CHAT_ID: str = os.getenv("TELEGRAM_CHAT_ID", "")
SEND_RECOVERY_ALERTS: bool = os.getenv("SEND_RECOVERY_ALERTS", "true").lower() == "true"

_MAX_RETRIES = 3
_BASE_DELAY = 1.0  # seconds — exponential backoff base


# ─── Message Formatting ─────────────────────────────────────

def _format_down_message(service_name: str, message: str, timestamp: str) -> str:
    """Build a Markdown-formatted DOWN alert."""
    return (
        "🔴 *SERVICE DOWN*\n"
        "━━━━━━━━━━━━━━━━━\n"
        f"📛 Service: `{_escape_md(service_name)}`\n"
        f"🕐 Waktu: `{_escape_md(timestamp)}`\n"
        f"❌ Error: `{_escape_md(message)}`\n"
        "━━━━━━━━━━━━━━━━━\n"
        "🏷 _Asentinel Monitor_"
    )


def _format_up_message(service_name: str, message: str, timestamp: str) -> str:
    """Build a Markdown-formatted RECOVERY alert."""
    return (
        "🟢 *SERVICE RECOVERED*\n"
        "━━━━━━━━━━━━━━━━━\n"
        f"✅ Service: `{_escape_md(service_name)}`\n"
        f"🕐 Waktu: `{_escape_md(timestamp)}`\n"
        f"📝 Info: `{_escape_md(message or 'Recovered')}`\n"
        "━━━━━━━━━━━━━━━━━\n"
        "🏷 _Asentinel Monitor_"
    )


def _escape_md(text: str) -> str:
    """Escape special Markdown characters for Telegram."""
    for char in ("_", "*", "`", "["):
        text = text.replace(char, f"\\{char}")
    return text


def _format_timestamp(raw: str) -> str:
    """Normalise an ISO timestamp to a human-friendly format."""
    if not raw:
        return datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    try:
        dt = datetime.fromisoformat(raw)
        return dt.strftime("%Y-%m-%d %H:%M:%S")
    except (ValueError, TypeError):
        return raw


# ─── Send Logic ──────────────────────────────────────────────

async def send_alert(
    service_name: str,
    status: str,
    message: str = "",
    timestamp: str = "",
) -> bool:
    """
    Send a Telegram alert for the given service event.

    Args:
        service_name: Name of the service (e.g. "API-Auth").
        status: "UP" or "DOWN".
        message: Human-readable description of the event.
        timestamp: ISO-8601 timestamp string.

    Returns:
        True if message was sent successfully, False otherwise.
    """
    if not TELEGRAM_BOT_TOKEN or not TELEGRAM_CHAT_ID:
        logger.error(
            "Telegram credentials not configured — "
            "set TELEGRAM_BOT_TOKEN and TELEGRAM_CHAT_ID"
        )
        return False

    status_upper = status.upper()
    ts_formatted = _format_timestamp(timestamp)

    # Decide whether to send
    if status_upper == "UP" and not SEND_RECOVERY_ALERTS:
        logger.info(
            "Recovery alert skipped (SEND_RECOVERY_ALERTS=false): %s", service_name
        )
        return False

    # Build message text
    if status_upper == "DOWN":
        text = _format_down_message(service_name, message, ts_formatted)
    else:
        text = _format_up_message(service_name, message, ts_formatted)

    return await _send_telegram_message(text)


async def _send_telegram_message(text: str) -> bool:
    """
    Post a message to Telegram with retry + exponential backoff.

    Returns True on success, False after all retries exhausted.
    """
    url = f"https://api.telegram.org/bot{TELEGRAM_BOT_TOKEN}/sendMessage"
    payload = {
        "chat_id": TELEGRAM_CHAT_ID,
        "text": text,
        "parse_mode": "Markdown",
        "disable_web_page_preview": True,
    }

    for attempt in range(1, _MAX_RETRIES + 1):
        try:
            async with httpx.AsyncClient(timeout=10.0) as client:
                response = await client.post(url, json=payload)

            if response.status_code == 200:
                data = response.json()
                if data.get("ok"):
                    logger.info("Telegram message sent successfully (attempt %d)", attempt)
                    return True
                else:
                    logger.warning(
                        "Telegram API returned ok=false: %s (attempt %d)",
                        data.get("description", "unknown"),
                        attempt,
                    )
            else:
                logger.warning(
                    "Telegram API HTTP %d (attempt %d): %s",
                    response.status_code,
                    attempt,
                    response.text[:200],
                )

        except httpx.TimeoutException:
            logger.warning("Telegram request timed out (attempt %d)", attempt)
        except httpx.HTTPError as exc:
            logger.warning(
                "Telegram HTTP error (attempt %d): %s", attempt, str(exc)
            )
        except Exception as exc:
            logger.error(
                "Unexpected error sending Telegram message (attempt %d): %s",
                attempt,
                str(exc),
            )

        # Exponential backoff before retry
        if attempt < _MAX_RETRIES:
            delay = _BASE_DELAY * (2 ** (attempt - 1))
            logger.info("Retrying in %.1fs…", delay)
            await asyncio.sleep(delay)

    logger.error("All %d Telegram send attempts failed", _MAX_RETRIES)
    return False
