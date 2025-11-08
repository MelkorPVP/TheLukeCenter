<?php

declare(strict_types=1);

/**
 * @param array<string, mixed> $query
 * @param array<string, mixed>|null $body
 * @return array<string, mixed>
 */
function google_http_request(
    string $url,
    array $query = [],
    ?array $body = null,
    string $method = 'GET',
    string $contentType = 'application/json'
): array {
    if (!empty($query)) {
        $separator = str_contains($url, '?') ? '&' : '?';
        $url .= $separator . http_build_query($query);
    }

    $headers = [];
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_TIMEOUT => 15,
    ];

    if ($method !== 'GET' && $body !== null) {
        if ($contentType === 'application/json') {
            $payload = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } else {
            $payload = http_build_query($body);
        }
        $options[CURLOPT_POSTFIELDS] = $payload;
        $headers[] = 'Content-Type: ' . $contentType;
    }

    if (!empty($headers)) {
        $options[CURLOPT_HTTPHEADER] = $headers;
    }

    $handle = curl_init();
    curl_setopt_array($handle, $options);
    $raw = curl_exec($handle);
    if ($raw === false) {
        $error = curl_error($handle);
        curl_close($handle);
        throw new RuntimeException('Google API request failed: ' . $error);
    }

    $status = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
    curl_close($handle);

    $decoded = json_decode($raw, true);
    if ($status >= 400) {
        $message = is_array($decoded) && isset($decoded['error']) ? json_encode($decoded['error']) : $raw;
        throw new RuntimeException('Google API error: ' . $message);
    }

    return is_array($decoded) ? $decoded : [];
}
