<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/config.php';

function app_require_public(string $file): void
{
    $environment = app_detect_environment();
    if (!defined('APP_ENVIRONMENT')) {
        define('APP_ENVIRONMENT', $environment);
    } else {
        $environment = APP_ENVIRONMENT;
    }

    $resolved = app_public_path($file, $environment);

    require $resolved;
}
