<?php

declare(strict_types=1);

$bootstrap = require __DIR__ . '/../bootstrap.php';

$config = $bootstrap['config'] ?? [];
$logger = $bootstrap['logger'] ?? null;

$exitCode = 0;
$environments = [APP_ENV_PROD, APP_ENV_TEST];

foreach ($environments as $environment)
{
    $envConfig = $config;
    $envConfig['environment'] = $environment;

    try
    {
        $payload = site_content_resolve_payload($envConfig, $logger);

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
        if ($logger instanceof AppLogger && $logger->isEnabled())
        {
            $logger->error('Content cache warmup failed', [
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
