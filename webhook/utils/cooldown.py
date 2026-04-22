"""
Asentinel Webhook — Anti-Spam Cooldown Manager

Prevents duplicate notifications for the same service within a configurable
time window.  Cooldown resets automatically on status transitions
(DOWN → UP or UP → DOWN) so recovery alerts always go through.
"""

import os
import time
from typing import Dict, Tuple

from utils.logger import logger


class CooldownManager:
    """
    Track per-service alert timestamps and enforce a minimum gap between
    consecutive notifications of the *same* status.

    Attributes:
        cooldown_seconds: Minimum seconds between duplicate alerts.
    """

    def __init__(self, cooldown_seconds: int | None = None):
        self.cooldown_seconds: int = cooldown_seconds or int(
            os.getenv("ALERT_COOLDOWN_SECONDS", "300")
        )
        # {service_name: (last_status, last_alert_epoch)}
        self._state: Dict[str, Tuple[str, float]] = {}
        logger.info(
            "CooldownManager initialized — cooldown=%ds", self.cooldown_seconds
        )

    # ─── Public API ──────────────────────────────────────────

    def can_send(self, service_name: str, status: str) -> bool:
        """
        Decide whether an alert is allowed right now.

        Cooldown is tracked **per-service** — different services never
        block each other.  Within the same service:

        1. First-ever alert → always allow.
        2. Status changed (e.g. DOWN → UP) → allow & reset timer.
        3. Same status within cooldown window → block (anti-spam).
        4. Same status after cooldown expired → allow & reset timer.
        """
        now = time.time()
        key = service_name.strip().lower()
        status_upper = status.upper()

        if key not in self._state:
            # First alert for this service — always allow
            self._state[key] = (status_upper, now)
            logger.debug("Cooldown PASS (first alert): %s [%s]", service_name, status)
            return True

        last_status, last_time = self._state[key]

        # Status transition (e.g. DOWN → UP) → always allow
        if last_status != status_upper:
            self._state[key] = (status_upper, now)
            logger.debug(
                "Cooldown PASS (status change %s→%s): %s",
                last_status,
                status_upper,
                service_name,
            )
            return True

        # Same service, same status — check elapsed time
        elapsed = now - last_time
        if elapsed >= self.cooldown_seconds:
            self._state[key] = (status_upper, now)
            logger.debug(
                "Cooldown PASS (expired, %.0fs elapsed): %s [%s]",
                elapsed,
                service_name,
                status,
            )
            return True

        remaining = self.cooldown_seconds - elapsed
        logger.info(
            "Cooldown BLOCK (%.0fs remaining): %s [%s]",
            remaining,
            service_name,
            status,
        )
        return False

    def reset(self, service_name: str) -> None:
        """Remove cooldown state for a specific service."""
        key = service_name.strip().lower()
        self._state.pop(key, None)
        logger.debug("Cooldown reset: %s", service_name)

    def reset_all(self) -> None:
        """Clear all cooldown state."""
        self._state.clear()
        logger.debug("All cooldowns reset")

    def get_status(self) -> Dict[str, dict]:
        """Return current cooldown state for all tracked services."""
        now = time.time()
        result = {}
        for key, (status, last_time) in self._state.items():
            elapsed = now - last_time
            result[key] = {
                "last_status": status,
                "last_alert_ago_seconds": round(elapsed, 1),
                "cooldown_remaining_seconds": max(
                    0, round(self.cooldown_seconds - elapsed, 1)
                ),
            }
        return result


# ─── Module-level singleton ──────────────────────────────────
cooldown_manager = CooldownManager()
