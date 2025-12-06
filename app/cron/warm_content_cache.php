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
    $message = $e->getMessage();

    if ($logger instanceof AppLogger) {
        $logger->error('Content cache warmup failed', ['error' => $message]);
    }

    fwrite(STDERR, 'Content cache warmup failed: ' . $message . "\n");

    // The cron job uses this script; when required GOOGLE_* env vars are missing, spell out
    // that the warmup (and thus cron) will keep failing until those values are provided.
    if ($e instanceof RuntimeException) {
        fwrite(
            STDERR,
            "Set the needed Google Sheet env vars (e.g., GOOGLE_SITE_VALUES_SHEET_ID_*) before rerunning warm_content_cache.php.\n"
        );
    }

    exit(1);
}
