<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/services/Logger.php';
require_once __DIR__ . '/services/GoogleService.php';
require_once __DIR__ . '/services/ContentService.php';
require_once __DIR__ . '/services/FormService.php';

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$environment = app_detect_environment();
if (!defined('APP_ENVIRONMENT')) {
    define('APP_ENVIRONMENT', $environment);
}

$config = app_build_configuration($environment);
$logger = new AppLogger(
    (bool) ($config['logging']['enabled'] ?? false),
    $config['logging']['file'] ?? (APP_ROOT . '/storage/logs/application.log'),
    $environment
);

$override = app_logging_override($config, function () use ($config, $logger) {
    return site_content_values($config, $logger);
});
$logger->setEnabled($override);

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

