<?php

declare(strict_types=1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $container = require __DIR__ . '/../bootstrap.php';
    $config = $container['config'];

    $input = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($input)) {
        throw new \InvalidArgumentException('Invalid request payload.');
    }

    handle_application_submission($config, $input);

    echo json_encode(['ok' => true]);
} catch (\InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
    ]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Unable to submit the form at this time.',
    ]);
}
