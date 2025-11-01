<?php

declare(strict_types=1);

require_once __DIR__ . '/google_http.php';

/**
 * @param array<string, mixed> $config
 */
function gmail_send_message(array $config, string $from, array $to, string $subject, string $body): void
{
    $apiKey = trim((string) ($config['api_key'] ?? ''));
    if ($apiKey === '') {
        throw new RuntimeException('Google API key is not configured.');
    }

    $sender = trim($from);
    if ($sender === '') {
        throw new RuntimeException('Gmail sender address is not configured.');
    }

    if (empty($to)) {
        throw new RuntimeException('Email recipient list is empty.');
    }

    $headers = [
        'From' => $sender,
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

    $url = sprintf('https://gmail.googleapis.com/gmail/v1/users/%s/messages/send', rawurlencode($sender));

    google_http_request(
        $url,
        ['key' => $apiKey],
        ['raw' => $raw],
        'POST'
    );
}
