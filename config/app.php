<?php

return [
    'name' => $_ENV['APP_NAME'] ?? 'Plataforma Documental',
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'America/Bogota',
    'locale' => $_ENV['APP_LOCALE'] ?? 'es',
    'key' => $_ENV['APP_KEY'] ?? '',
];
