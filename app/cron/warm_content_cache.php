<?php

declare(strict_types=1);

$bootstrap = require __DIR__ . '/../bootstrap.php';

// Preserve the shared logging config, but rebuild per-environment configs so each
// warmup run uses its own Google sheet IDs instead of whichever environment was
// detected when bootstrap.php ran.
$loggingConfig = $bootstrap['config']['logging'] ?? [];

$exitCode = 0;
$environments = [APP_ENV_PROD, APP_ENV_TEST];

foreach ($environments as $environment)
{
    $envConfig = app_build_configuration($environment);
    $envLogger = new AppLogger(
        (bool) ($loggingConfig['enabled'] ?? false),
        $loggingConfig['file'] ?? (APP_ROOT . '/storage/logs/application.log'),
        $environment
    );

    try
    {
        // Always fetch fresh content to avoid any in-memory/static caches.
        $payload = site_content_fetch_payload($envConfig, $envLogger);
        site_content_save_cache($envConfig, $payload, $envLogger);

        $valuesCount = is_array($payload['values'] ?? null) ? count($payload['values']) : 0;
        $testimonialsCount = is_array($payload['testimonials'] ?? null) ? count($payload['testimonials']) : 0;
        $imagesCount = is_array($payload['images'] ?? null) ? count($payload['images']) : 0;

        $generatedAtRaw = $payload['generated_at'] ?? null;
        $generatedAt = is_numeric($generatedAtRaw)
            ? date('c', (int) $generatedAtRaw)
            : (string) ($generatedAtRaw ?? '');

        fwrite(
            STDOUT,
            sprintf(
                "Content cache ready for %s (values: %d, testimonials: %d, images: %d, generated_at: %s)\n",
                $environment,
                $valuesCount,
                $testimonialsCount,
                $imagesCount,
                $generatedAt
            )
        );
    }
    catch (Throwable $e)
    {
        if ($envLogger->isEnabled())
        {
            $envLogger->error('Content cache warmup failed', [
                'environment' => $environment,
                'exception' => get_class($e),
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        fwrite(
            STDERR,
            sprintf(
                "Error warming content cache for %s: %s\n",
                $environment,
                $e->getMessage()
            )
        );

        $exitCode = 1;
    }
}

exit($exitCode);
