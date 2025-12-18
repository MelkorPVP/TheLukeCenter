<?php

declare(strict_types=1);

/**
 * Commenting convention:
 * - Docblocks summarize function intent along with key inputs/outputs.
 * - Inline context comments precede major initialization, configuration, or external calls.
 */

// --- START: Cron Environment Fixer ---

// Identify whether the cron script lives in the production or test tree so we can configure paths dynamically.
$scriptRoot = realpath(dirname(__DIR__, 2));
$rootName = $scriptRoot !== false ? basename($scriptRoot) : '';

// Choose the document root and host based on the detected tree; fall back to production-friendly defaults.
$_SERVER['DOCUMENT_ROOT'] = $scriptRoot ?: '/home1/bnrortmy/public_html';
$_SERVER['HTTP_HOST'] = $rootName === 'test.thelukecenter.org'
    ? 'test.thelukecenter.org'
    : 'www.thelukecenter.org';
$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'];
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

// Align working directory with the detected web root so relative paths match production requests.
if (is_dir($_SERVER['DOCUMENT_ROOT'])) {
    chdir($_SERVER['DOCUMENT_ROOT']);
}

// Load environment overrides from the account-level .htaccess so API credentials are available to the cron job.
$htaccessPath = realpath($_SERVER['DOCUMENT_ROOT'] . '/../.htaccess') ?: '/home1/bnrortmy/.htaccess';
if (file_exists($htaccessPath)) {
    $lines = file($htaccessPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (preg_match('/^\s*SetEnv\s+([A-Za-z0-9_]+)\s+(?:\"([^\"]+)\"|([^\s]+))/', $line, $matches)) {
            $key = $matches[1];
            $value = !empty($matches[2]) ? $matches[2] : ($matches[3] ?? '');
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// --- END: Cron Environment Fixer ---

$bootstrap = require __DIR__ . '/../bootstrap.php';

// Pre-flight check ensures the token file is reachable before contacting Google.
$tokenPath = google_get_token_path($bootstrap['config']['google'] ?? []);

// Preserve the shared logging config, but rebuild per-environment configs.
$loggingConfig = $bootstrap['config']['logging'] ?? [];
$sharedWriter = app_logger_file_writer($loggingConfig['file'] ?? (APP_ROOT . '/storage/logs/application.log'));
$bootstrapLogger = app_logger_from_config($loggingConfig, APP_ENVIRONMENT, $sharedWriter);

// Record token availability so operations staff can diagnose cron permission issues quickly.
if ($bootstrapLogger->isEnabled()) {
    if (!file_exists($tokenPath)) {
        $bootstrapLogger->error('Token file not found for cron warmup', [
            'token_path' => $tokenPath,
            'cwd' => getcwd(),
        ]);
    } elseif (!is_readable($tokenPath)) {
        $bootstrapLogger->error('Token file not readable for cron warmup', [
            'token_path' => $tokenPath,
        ]);
    } else {
        $bootstrapLogger->info('Token file available for cron warmup', [
            'token_path' => $tokenPath,
        ]);
    }
}

$exitCode = 0;
$environments = [APP_ENV_PROD, APP_ENV_TEST];

foreach ($environments as $environment) {
    $envConfig = app_build_configuration($environment);
    $envLogger = app_logger_from_config($loggingConfig, $environment, $sharedWriter);

    try {
        // Each environment refreshes independently so cache issues remain isolated.
        $envLogger->info('Starting content cache warmup', [
            'environment' => $environment,
        ]);

        $payload = site_content_fetch_payload($envConfig, $envLogger);
        site_content_save_cache($envConfig, $payload, $envLogger);

        $valuesCount = is_array($payload['values'] ?? null) ? count($payload['values']) : 0;
        $testimonialsCount = is_array($payload['testimonials'] ?? null) ? count($payload['testimonials']) : 0;
        $imagesCount = is_array($payload['programImages'] ?? null) ? count($payload['programImages']) : 0;
        $alumniImagesCount = is_array($payload['alumniImages'] ?? null) ? count($payload['alumniImages']) : 0;

        $generatedAtRaw = $payload['generated_at'] ?? null;
        $generatedAt = is_numeric($generatedAtRaw)
            ? date('c', (int) $generatedAtRaw)
            : (string) ($generatedAtRaw ?? '');

        $envLogger->info('Content cache ready', [
            'environment' => $environment,
            'values_count' => $valuesCount,
            'testimonials_count' => $testimonialsCount,
            'images_count' => $imagesCount,
            'alumni_images_count' => $alumniImagesCount,
            'generated_at' => $generatedAt,
        ]);
    } catch (Throwable $e) {
        if ($envLogger->isEnabled()) {
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

        $exitCode = 1;
    }
}

exit($exitCode);
