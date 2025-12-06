<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../services/Logger.php';
require_once __DIR__ . '/../services/GoogleService.php';
require_once __DIR__ . '/../services/ContentService.php';

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__, 1));
}

/**
 * Warm the cache for a specific environment.
 *
 * @return array{environment:string,success:bool,message?:string,values?:int,testimonials?:int,images?:int,generated_at?:string}
 */
function warm_environment(string $environment): array
{
    $config = app_build_configuration($environment);
    $logger = new AppLogger(
        (bool) ($config['logging']['enabled'] ?? false),
        $config['logging']['file'] ?? (APP_ROOT . '/storage/logs/application.log'),
        $environment
    );

    if ($logger->isEnabled()) {
        $logger->info('Starting content cache warmup', ['environment' => $environment]);
    }

    try {
        $payload = site_content_fetch_payload($config, $logger);
        site_content_save_cache($config, $payload, $logger);

        $summary = [
            'environment' => $environment,
            'success' => true,
            'values' => count($payload['values'] ?? []),
            'testimonials' => count($payload['testimonials'] ?? []),
            'images' => count($payload['images'] ?? []),
            'generated_at' => date('c', (int) ($payload['generated_at'] ?? time())),
        ];

        if ($logger->isEnabled()) {
            $logger->info('Content cache warmup complete', $summary);
        }

        return $summary;
    } catch (Throwable $e) {
        $message = $e->getMessage();

        if ($logger->isEnabled()) {
            $logger->error('Content cache warmup failed', [
                'environment' => $environment,
                'error' => $message,
            ]);
        }

        return [
            'environment' => $environment,
            'success' => false,
            'message' => $message,
        ];
    }
}

$results = [];
foreach ([APP_ENV_PROD, APP_ENV_TEST] as $env) {
    $results[] = warm_environment($env);
}

$exitCode = 0;
foreach ($results as $result) {
    if ($result['success'] ?? false) {
        fwrite(STDOUT, sprintf(
            "Content cache refreshed for %s.\n- values: %d\n- testimonials: %d\n- images: %d\n- generated_at: %s\n",
            $result['environment'],
            (int) ($result['values'] ?? 0),
            (int) ($result['testimonials'] ?? 0),
            (int) ($result['images'] ?? 0),
            (string) ($result['generated_at'] ?? '')
        ));
    } else {
        $exitCode = 1;
        fwrite(STDERR, sprintf(
            "Content cache warmup failed for %s: %s\n",
            (string) ($result['environment'] ?? ''),
            (string) ($result['message'] ?? 'unknown error')
        ));
    }
}

exit($exitCode);
