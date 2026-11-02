<?php

// Vercel read-only kecuali /tmp
foreach ([
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/bootstrap/cache',
] as $dir) {
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

$env = [
    'VIEW_COMPILED_PATH'  => '/tmp/storage/framework/views',
    'APP_SERVICES_CACHE'  => '/tmp/bootstrap/cache/services.php',
    'APP_PACKAGES_CACHE'  => '/tmp/bootstrap/cache/packages.php',
    'APP_CONFIG_CACHE'    => '/tmp/bootstrap/cache/config.php',
    'APP_ROUTES_CACHE'    => '/tmp/bootstrap/cache/routes.php',
    'APP_EVENTS_CACHE'    => '/tmp/bootstrap/cache/events.php',
];
foreach ($env as $k => $v) {
    putenv("$k=$v");
    $_ENV[$k] = $_SERVER[$k] = $v;
}

require __DIR__ . '/../public/index.php';