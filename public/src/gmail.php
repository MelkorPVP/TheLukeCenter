<?php

declare(strict_types=1);

require_once __DIR__ . '/google_auth.php';

/**
 * @param array<string, mixed> $config
 */
function gmail_send_message(array $config, string $from, array $to, string $subject, string $body): void
{
    if ($from === '' || empty($to)) {
        throw new RuntimeException('Email configuration is incomplete.');
    }

    $token = google_service_account_token($config, ['https://www.googleapis.com/auth/gmail.send']);

    $headers = [
        'From' => $from,
        'To' => implode(', ', $to),
        'Subject' => $subject,
        'Content-Type' => 'text/plain; charset=utf-8',
    ];

    $message = '';
    foreach ($headers as $key => $value) {
        $message .= $key . ': ' . $value . "\r\n";
    }
    $message .= "\r\n" . $body;

    $raw = rtrim(strtr(base64_encode($message), '+/', '-_'), '=');

    $url = 'https://gmail.googleapis.com/gmail/v1/users/me/messages/send';

    google_http_request(
        $url,
        ['raw' => $raw],
        $token,
        'POST'
    );
}
