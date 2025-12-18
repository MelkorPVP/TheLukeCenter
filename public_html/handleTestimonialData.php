<?php
    
    declare(strict_types=1);
    
    $container = require_once __DIR__ . '/app/bootstrap.php';
    
    $config = $container['config'] ?? [];
    $logger = $container['logger'] ?? null;

    header('Content-Type: application/json; charset=utf-8');

    try {
        $payload = site_content_resolve_payload($config, $logger);

        $images = site_content_gallery_program_images($config, $logger);
        $testimonials = site_content_testimonials($config, $logger);
        $alumniImages = site_content_alumni_images($config, $logger);

        if ($logger instanceof AppLogger && $logger->isEnabled()) {
            $logger->info('Gallery data response ready', [
                'request_id' => $logger->getRequestId(),
                'testimonial_count' => count($testimonials),
                'cache_generated_at' => $payload['generated_at'] ?? null,
            ]);
        }

        echo json_encode([
        'testimonials' => $testimonials,
        ], JSON_UNESCAPED_SLASHES);

        } catch (Throwable $e) {
        http_response_code(500);
        if ($logger instanceof AppLogger) {
            $logger->error('Gallery data error', ['error' => $e->getMessage()]);
        }
        
        echo json_encode([
        'testimonials' => [],
        'error' => 'Failed to load gallery data',
        ]);
    }
