<?php

return [
    'default' => $_ENV['FILESYSTEM_DISK'] ?? 'local',
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => BASE_PATH . '/storage',
        ],
        'public' => [
            'driver' => 'local',
            'root' => BASE_PATH . '/uploads',
            'url' => ($_ENV['APP_URL'] ?? '') . '/uploads',
            'visibility' => 'public',
        ],
    ],
];
