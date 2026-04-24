"""
Asentinel Webhook — Logging Configuration

Dual handler logging: console (colored) + rotating file.
"""

import logging
import os
from logging.handlers import RotatingFileHandler
from pathlib import Path


def setup_logger(name: str = "asentinel", level: str = "INFO") -> logging.Logger:
    """
    Configure and return a logger with console + rotating file handlers.

    Args:
        name: Logger name identifier.
        level: Log level string (DEBUG, INFO, WARNING, ERROR, CRITICAL).

    Returns:
        Configured logging.Logger instance.
    """
    logger = logging.getLogger(name)

    # Prevent duplicate handlers on re-initialization
    if logger.handlers:
        return logger

    log_level = getattr(logging, level.upper(), logging.INFO)
    logger.setLevel(log_level)

    # ─── Formatter ───────────────────────────────────────────
    fmt = "[%(asctime)s] [%(levelname)-8s] %(name)s — %(message)s"
    date_fmt = "%Y-%m-%d %H:%M:%S"
    formatter = logging.Formatter(fmt, datefmt=date_fmt)

    # ─── Console Handler ────────────────────────────────────
    console_handler = logging.StreamHandler()
    console_handler.setLevel(log_level)
    console_handler.setFormatter(formatter)
    logger.addHandler(console_handler)

    # ─── File Handler (Rotating) ─────────────────────────────
    log_dir = Path(__file__).parent.parent / "logs"
    log_dir.mkdir(parents=True, exist_ok=True)
    log_file = log_dir / "webhook.log"

    file_handler = RotatingFileHandler(
        filename=str(log_file),
        maxBytes=5 * 1024 * 1024,  # 5 MB per file
        backupCount=3,
        encoding="utf-8",
    )
    file_handler.setLevel(log_level)
    file_handler.setFormatter(formatter)
    logger.addHandler(file_handler)

    logger.info("Logger initialized — level=%s, file=%s", level, log_file)
    return logger


# ─── Module-level singleton ──────────────────────────────────
log_level = os.getenv("LOG_LEVEL", "INFO")
logger = setup_logger("asentinel", log_level)
