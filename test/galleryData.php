<?php
    
    declare(strict_types=1);
    
    $container = require __DIR__ . '/../app/bootstrap.php';
    
    $config = $container['config'] ?? [];
    $logger = $container['logger'] ?? null;

    header('Content-Type: application/json; charset=utf-8');

    try {
        $googleConfig = $config['google'] ?? [];

        $folderId = (string) ($config['google']['gallery_folder_id'] ?? '');
        if ($folderId === '') {
            echo json_encode([
            'images' => [],
            'testimonials' => site_content_testimonials($config, $logger),
            ]);
            exit;
        }

        if ($logger instanceof AppLogger && $logger->isEnabled()) {
            $logger->info('Gallery data request', [
                'request_id' => $logger->getRequestId(),
                'folder_id' => $folderId,
            ]);
        }

        $files = google_drive_list_images_in_folder($googleConfig, $folderId, 80, $logger);

        // Sort by file name so the order is deterministic for caching and rotations.
        usort($files, static function (array $a, array $b): int {
            return strcmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
        });

        $images = [];
        foreach ($files as $f) {
            $id   = (string) ($f['id'] ?? '');
            $name = (string) ($f['name'] ?? '');

            if ($id === '') continue;

            $images[] = [
            'id' => $id,
            'name' => $name,
            'url' => google_drive_build_image_url($id, 1600),
            ];
        }

        // Remove duplicate IDs to prevent broken rotations when Drive contains aliases.
        $images = array_values(array_unique($images, SORT_REGULAR));

        $testimonials = site_content_testimonials($config, $logger);

        if ($logger instanceof AppLogger && $logger->isEnabled()) {
            $logger->info('Gallery data response ready', [
                'request_id' => $logger->getRequestId(),
                'image_count' => count($images),
                'testimonial_count' => count($testimonials),
            ]);
        }

        echo json_encode([
        'images' => $images,
        'testimonials' => $testimonials,
        ], JSON_UNESCAPED_SLASHES);
        
        } catch (Throwable $e) {
        http_response_code(500);
        if ($logger instanceof AppLogger) {
            $logger->error('Gallery data error', ['error' => $e->getMessage()]);
        }
        
        echo json_encode([
        'images' => [],
        'testimonials' => [],
        'error' => 'Failed to load gallery data',
        ]);
    }
