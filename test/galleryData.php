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
        
        $files = google_drive_list_images_in_folder($googleConfig, $folderId, 80, $logger);
        
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
        
        $testimonials = site_content_testimonials($config, $logger);
        
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
