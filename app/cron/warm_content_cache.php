<?php

declare(strict_types=1);

$container = require __DIR__ . '/../bootstrap.php';

$config = $container['config'] ?? [];
$logger = $container['logger'] ?? null;

if ($logger instanceof AppLogger && $logger->isEnabled()) {
    $logger->info('Starting content cache warmup');
}

try {
    $payload = site_content_fetch_payload($config, $logger);
    site_content_save_cache($config, $payload, $logger);

    $summary = [
        'values_count' => count($payload['values'] ?? []),
        'testimonials_count' => count($payload['testimonials'] ?? []),
        'images_count' => count($payload['images'] ?? []),
        'generated_at' => date('c', (int) ($payload['generated_at'] ?? time())),
    ];

    if ($logger instanceof AppLogger && $logger->isEnabled()) {
        $logger->info('Content cache warmup complete', $summary);
    }

    fwrite(STDOUT, "Content cache refreshed.\n");
    foreach ($summary as $key => $value) {
        fwrite(STDOUT, sprintf("- %s: %s\n", $key, (string) $value));
    }

    exit(0);
} catch (Throwable $e) {
    if ($logger instanceof AppLogger) {
        $logger->error('Content cache warmup failed', ['error' => $e->getMessage()]);
    }

    fwrite(STDERR, 'Content cache warmup failed: ' . $e->getMessage() . "\n");
    exit(1);
}
