<?php

declare(strict_types=1);

/**
 * Commenting convention:
 * - Docblocks summarize function intent along with key inputs/outputs.
 * - Inline context comments precede major initialization, configuration, or external calls.
 */

// Load shared service definitions used throughout the application lifecycle.
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/services/Logger.php';
require_once __DIR__ . '/services/GoogleService.php';
require_once __DIR__ . '/services/ContentService.php';
require_once __DIR__ . '/services/FormService.php';
require_once __DIR__ . '/services/DeveloperService.php';

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__ , 2));
}

// Ensure all entry points share a consistent session state for logging and forms.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();

    // Rotate a fresh CSRF token at the beginning of each session lifecycle.
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Ensure a CSRF token is always available for form rendering when sessions were started elsewhere.
if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] === '') {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Detect the runtime environment before constructing configuration or loggers.
$environment = app_detect_environment();
if (!defined('APP_ENVIRONMENT')) {
    define('APP_ENVIRONMENT', $environment);
}

// Build configuration and logging pipeline once per request for reuse.
$config = app_build_configuration($environment);
$loggerWriter = app_logger_file_writer($config['logging']['file'] ?? (APP_ROOT . '/storage/logs/application.log'));
$logger = app_logger_from_config(
    $config['logging'] ?? [],
    $environment,
    $loggerWriter
);

// Log the request bootstrap step so we can trace from init through completion.
if ($logger->isEnabled()) {
    $logger->info('Request bootstrap complete', [
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
        'uri' => $_SERVER['REQUEST_URI'] ?? '',
        'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? '',
        'session_id' => session_id(),
    ]);
}

return [
    'config' => $config,
    'logger' => $logger,
];

