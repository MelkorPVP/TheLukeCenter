<?php
    
    declare(strict_types=1);
    
    // --- START: Cron Environment Fixer ---
    
    // 1. Mock Web Environment Variables
    // Some OAuth libraries require HTTP_HOST or SERVER_NAME to be set.
    $_SERVER['DOCUMENT_ROOT'] = '/home1/bnrortmy/public_html';
    $_SERVER['HTTP_HOST'] = 'www.thelukecenter.org'; // Adjust if your domain is different
    $_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'];
    $_SERVER['REQUEST_URI'] = '/';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    
    // 2. Fix Working Directory
    // Force the script to "stand" in public_html, just like the web server does.
    // This fixes relative paths like "../tokenStorage"
    if (is_dir($_SERVER['DOCUMENT_ROOT'])) {
        chdir($_SERVER['DOCUMENT_ROOT']);
    }
    
    // 3. Load Variables from .htaccess
    $htaccessPath = '/home1/bnrortmy/.htaccess';
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
    
    // 4. Pre-flight Debug: Check Token Accessibility
    // This will print a clear error to your log if the file is physically unreadable
    $tokenPath = '/home1/bnrortmy/tokenStorage/google-api-oauth-token.json';
    if (!file_exists($tokenPath)) {
        fwrite(STDERR, "CRITICAL DEBUG: Token file not found at: $tokenPath\n");
        fwrite(STDERR, "Current Working Dir: " . getcwd() . "\n");
        } elseif (!is_readable($tokenPath)) {
        fwrite(STDERR, "CRITICAL DEBUG: Token file exists but is NOT readable. Check permissions.\n");
    }
    // --- END: Cron Environment Fixer ---
    
    
    $bootstrap = require __DIR__ . '/../bootstrap.php';
    
    // Preserve the shared logging config, but rebuild per-environment configs
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