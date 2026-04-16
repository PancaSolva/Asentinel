<?php

return [
    // ADDED: alerting system configuration.
    'email_from' => env('ALERT_EMAIL_FROM'),
    'email_to' => env('ALERT_EMAIL_TO'),
    'cooldown_hours' => (int) env('ALERT_EMAIL_COOLDOWN_HOURS', 24),
    'mailer' => env('ALERT_EMAIL_MAILER', 'alert_gmail'),
    'cooldown_store' => env('ALERT_EMAIL_COOLDOWN_STORE', 'file'),
];
