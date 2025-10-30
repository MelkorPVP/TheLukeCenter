<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/content.php';
require_once __DIR__ . '/includes/forms.php';

try {
    $config = require __DIR__ . '/config/app.php';
} catch (\Throwable $e) {
    if (PHP_SAPI === 'cli') {
        throw $e;
    }
    http_response_code(500);
    echo '<h1>Configuration error</h1>';
    exit;
}

return [
    'config' => $config,
];
