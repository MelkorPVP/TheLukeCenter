<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input') ?: '[]', true);
$username = trim((string) ($input['username'] ?? ''));
$password = (string) ($input['password'] ?? '');
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

$attemptKey = $ipAddress . '|' . ($username !== '' ? $username : 'unknown');
$attempts = $_SESSION['developer_login_attempts'][$attemptKey] ?? [
    'attempts' => 0,
    'lock_until' => 0,
    'last_attempt' => 0,
];
$now = time();
$baseBackoffSeconds = 30;
$backoffCeilingSeconds = 15 * 60;
$attemptsBeforeBackoff = 3;

if (($attempts['lock_until'] ?? 0) > $now) {
    $retryAfter = (int) max(1, ($attempts['lock_until'] - $now));

    if ($logger->isEnabled()) {
        $logger->warning('Developer login throttled', [
            'username' => $username,
            'ip' => $ipAddress,
            'retry_after' => $retryAfter,
            'attempts' => $attempts['attempts'],
        ]);
    }

    http_response_code(429);
    header('Retry-After: ' . $retryAfter);
    echo json_encode([
        'success' => false,
        'error' => 'Too many login attempts. Please try again later.',
    ]);
    exit;
}

try {
    if ($username === '' || $password === '') {
        throw new InvalidArgumentException('Username and password are required.');
    }

    $isValid = developer_validate_credentials($config, $username, $password, $logger);
    if (!$isValid) {
        throw new RuntimeException('Invalid developer credentials.');
    }

    developer_start_session($username);

    unset($_SESSION['developer_login_attempts'][$attemptKey]);

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

    $attempts['attempts'] = ($attempts['attempts'] ?? 0) + 1;
    $attempts['last_attempt'] = $now;

    if ($attempts['attempts'] >= $attemptsBeforeBackoff) {
        $penaltyExponent = $attempts['attempts'] - $attemptsBeforeBackoff;
        $lockoutSeconds = (int) min($backoffCeilingSeconds, $baseBackoffSeconds * (2 ** $penaltyExponent));
        $attempts['lock_until'] = $now + $lockoutSeconds;

        if ($logger->isEnabled()) {
            $logger->warning('Developer login attempt rate-limited', [
                'username' => $username,
                'ip' => $ipAddress,
                'attempts' => $attempts['attempts'],
                'lockout_seconds' => $lockoutSeconds,
            ]);
        }
    }

    $_SESSION['developer_login_attempts'][$attemptKey] = $attempts;

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ]);
}

