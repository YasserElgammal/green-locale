<?php

return [
    'default_locale' => $_ENV['APP_LOCALE'] ?? 'en',
    'fallback_locale' => $_ENV['APP_FALLBACK_LOCALE'] ?? 'en',
    'available_locales' => [
        'en',
        'ar',
    ],
    'resolver' => 'request',
    'strict' => false,
];
