<?php

declare(strict_types=1);

/**
 * @param array{credentials_path:string|null, delegated_user:?string} $config
 * @param array<int, string> $scopes
 */
function google_service_account_token(array $config, array $scopes): string
{
    $credentialsPath = $config['credentials_path'] ?? null;
    if (empty($credentialsPath) || !is_file($credentialsPath)) {
        throw new RuntimeException('Google credentials file not found.');
    }

    $credentials = json_decode((string) file_get_contents($credentialsPath), true);
    if (!is_array($credentials)) {
        throw new RuntimeException('Invalid Google credentials file.');
    }

    $clientEmail = $credentials['client_email'] ?? null;
    $privateKey = $credentials['private_key'] ?? null;
    $tokenUri = $credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token';
    if (!$clientEmail || !$privateKey) {
        throw new RuntimeException('Google credentials are missing required fields.');
    }

    sort($scopes);
    $cacheKey = md5($clientEmail . '|' . implode(' ', $scopes) . '|' . ($config['delegated_user'] ?? ''));

    static $tokenCache = [];
    if (isset($tokenCache[$cacheKey])) {
        $cached = $tokenCache[$cacheKey];
        if ($cached['expires_at'] > time() + 60) {
            return $cached['token'];
        }
    }

    $now = time();
    $jwtHeader = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
    $jwtClaimSet = [
        'iss' => $clientEmail,
        'sub' => $config['delegated_user'] ?: $clientEmail,
        'aud' => $tokenUri,
        'iat' => $now,
        'exp' => $now + 3600,
        'scope' => implode(' ', $scopes),
    ];
    $jwtPayload = base64_encode(json_encode($jwtClaimSet, JSON_THROW_ON_ERROR));
    $jwtUnsigned = str_replace(['+', '/', '='], ['-', '_', ''], $jwtHeader) . '.' . str_replace(['+', '/', '='], ['-', '_', ''], $jwtPayload);

    if (!openssl_sign($jwtUnsigned, $signature, $privateKey, 'sha256')) {
        throw new RuntimeException('Failed to sign Google service account token.');
    }

    $jwtAssertion = $jwtUnsigned . '.' . str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    $response = google_http_request(
        $tokenUri,
        [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwtAssertion,
        ],
        null,
        'POST',
        'application/x-www-form-urlencoded'
    );

    $accessToken = $response['access_token'] ?? null;
    $expiresIn = (int) ($response['expires_in'] ?? 3600);
    if (!$accessToken) {
        throw new RuntimeException('Unable to obtain Google access token.');
    }

    $tokenCache[$cacheKey] = [
        'token' => $accessToken,
        'expires_at' => $now + $expiresIn,
    ];

    return $accessToken;
}

/**
 * @param array<string, mixed> $data
 * @return array<string, mixed>
 */
function google_http_request(string $url, array $data = [], ?string $bearerToken = null, string $method = 'POST', string $contentType = 'application/json'): array
{
    $headers = [];
    if ($method !== 'GET') {
        $headers[] = 'Content-Type: ' . $contentType;
    }
    if ($bearerToken) {
        $headers[] = 'Authorization: Bearer ' . $bearerToken;
    }

    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 15,
    ];

    if ($method === 'GET') {
        if ($data) {
            $options[CURLOPT_URL] = $url . (str_contains($url, '?') ? '&' : '?') . http_build_query($data);
        }
    } else {
        if ($contentType === 'application/x-www-form-urlencoded') {
            $options[CURLOPT_POSTFIELDS] = http_build_query($data);
        } else {
            $options[CURLOPT_POSTFIELDS] = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
    }

    $ch = curl_init();
    curl_setopt_array($ch, $options);
    $raw = curl_exec($ch);
    if ($raw === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('Google API request failed: ' . $error);
    }
    $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    $decoded = json_decode($raw, true);
    if ($status >= 400) {
        $message = is_array($decoded) && isset($decoded['error']) ? json_encode($decoded['error']) : $raw;
        throw new RuntimeException('Google API error: ' . $message);
    }

    return is_array($decoded) ? $decoded : [];
}
