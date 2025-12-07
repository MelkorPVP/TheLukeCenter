<?php
    
    declare(strict_types=1);
    
    // --- START: Load Environment Variables from .htaccess ---
    // We must do this BEFORE requiring bootstrap.php so the environment is ready
    $htaccessPath = '/home1/bnrortmy/.htaccess';
    
    if (file_exists($htaccessPath)) {
        // Read file into an array, skipping empty lines
        $lines = file($htaccessPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Look for lines starting with "SetEnv"
            // Regex captures: 1=Key, 2=Value inside quotes, 3=Value without quotes
            if (preg_match('/^\s*SetEnv\s+([A-Za-z0-9_]+)\s+(?:\"([^\"]+)\"|([^\s]+))/', $line, $matches)) {
                $key = $matches[1];
                // If match[2] is empty, use match[3] (unquoted value)
                $value = !empty($matches[2]) ? $matches[2] : ($matches[3] ?? '');
                
                // Populate PHP environment variables so getenv() and $_ENV work
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
    // --- END: Load Environment Variables from .htaccess ---
    
    $bootstrap = require __DIR__ . '/../bootstrap.php';
    
    // Preserve the shared logging config, but rebuild per-environment configs so each
    // warmup run uses its own Google sheet IDs instead of whichever environment was
    // detected when bootstrap.php ran.
    $loggingConfig = $bootstrap['config']['logging'] ?? [];
    
    $exitCode = 0;
    // Note: APP_ENV_PROD and APP_ENV_TEST are assumed to be defined in bootstrap.php
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