<?php
    
    declare(strict_types=1);
    
    // Reuse the shared bootstrap so environment detection, logger defaults, and
    // service includes stay consistent with the application.
    $bootstrap = require __DIR__ . '/../bootstrap.php';
    
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
            $type        = get_class($e);
            $code        = (string) $e->getCode();
            $baseMessage = trim((string) $e->getMessage());
            $location    = sprintf('%s:%d', $e->getFile(), $e->getLine());
            
            // Build a detailed, CLI-friendly message
            $message = $type;
            if ($code !== '' && $code !== '0') {
                $message .= " (code {$code})";
            }
            $message .= " at {$location}";
            if ($baseMessage !== '') {
                $message .= " - {$baseMessage}";
                } else {
                $message .= " - (no exception message provided)";
            }
            
            // Include up to 3 previous exceptions, if present
            $prev  = $e->getPrevious();
            $depth = 0;
            while ($prev && $depth < 3) {
                $prevType = get_class($prev);
                $prevCode = (string) $prev->getCode();
                $prevMsg  = trim((string) $prev->getMessage());
                $prevLoc  = sprintf('%s:%d', $prev->getFile(), $prev->getLine());
                
                $message .= ' | previous: ' . $prevType;
                if ($prevCode !== '' && $prevCode !== '0') {
                    $message .= " (code {$prevCode})";
                }
                $message .= " at {$prevLoc}";
                if ($prevMsg !== '') {
                    $message .= " - {$prevMsg}";
                }
                
                $prev = $prev->getPrevious();
                $depth++;
            }
            
            // Rich log context for debugging (includes full trace)
            if ($logger->isEnabled()) {
                $logger->error('Content cache warmup failed', [
                'environment' => $environment,
                'exception'   => $type,
                'code'        => $e->getCode(),
                'message'     => $baseMessage,
                'file'        => $e->getFile(),
                'line'        => $e->getLine(),
                'trace'       => $e->getTraceAsString(),
                ]);
            }
            
            // Keep your existing hint, but now appended after detailed context
            if ($e instanceof RuntimeException) {
                $message .= ' | Set the needed Google Sheet env vars (e.g., GOOGLE_SITE_VALUES_SHEET_ID_*).';
            }
            
            return [
            'environment' => $environment,
            'success'     => false,
            'message'     => $message,
            ];
        }
    }
    
    $results = [];
    foreach ([APP_ENV_PROD, APP_ENV_TEST] as $env) 
    {
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
