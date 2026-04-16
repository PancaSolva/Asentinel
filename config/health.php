<?php

return [
    'service_name' => env('HEALTH_SERVICE_NAME', env('APP_NAME', 'application')),
    'log_channel' => env('HEALTH_LOG_CHANNEL', env('LOG_CHANNEL', 'stack')),
    'log_success' => env('HEALTH_LOG_SUCCESS', false),

    'external_api' => [
        'enabled' => env('HEALTH_EXTERNAL_API_ENABLED', false),
        'url' => env('HEALTH_EXTERNAL_API_URL', ''),
        'timeout' => (float) env('HEALTH_EXTERNAL_API_TIMEOUT', 2),
        'connect_timeout' => (float) env('HEALTH_EXTERNAL_API_CONNECT_TIMEOUT', 1),
    ],
];
