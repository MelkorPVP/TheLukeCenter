<?php

declare(strict_types=1);

$rootDir = dirname(__DIR__);

require_once $rootDir . '/vendor/autoload.php';

try {
    return require $rootDir . '/src/bootstrap.php';
} catch (\Throwable $e) {
    if (PHP_SAPI === 'cli') {
        throw $e;
    }

    http_response_code(500);
    echo '<h1>Configuration error</h1>';
    echo '<p>Please verify Google API credentials and spreadsheet settings.</p>';
    exit;
}
