<?php

declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input') ?: '[]', true);
$username = trim((string) ($input['username'] ?? ''));
$password = (string) ($input['password'] ?? '');

try {
    if ($username === '' || $password === '') {
        throw new InvalidArgumentException('Username and password are required.');
    }

    $isValid = developer_validate_credentials($config, $username, $password, $logger);
    if (!$isValid) {
        throw new RuntimeException('Invalid developer credentials.');
    }

    developer_start_session($username);

    if ($logger->isEnabled()) {
        $logger->info('Developer login successful', [
            'username' => $username,
            'session_id' => session_id(),
        ]);
    }

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    if ($logger->isEnabled()) {
        $logger->error('Developer login failed', [
            'message' => $e->getMessage(),
        ]);
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ]);
}

