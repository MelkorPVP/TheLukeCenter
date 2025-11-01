<?php

declare(strict_types=1);

require_once __DIR__ . '/google_http.php';

/**
 * @param array<string, mixed> $config
 * @return array<int, array<int, string>>
 */
function google_sheets_get_values(array $config, string $spreadsheetId, string $range): array
{
    if ($spreadsheetId === '') {
        throw new RuntimeException('Google Sheets spreadsheet ID is not configured.');
    }

    $apiKey = trim((string) ($config['api_key'] ?? ''));
    if ($apiKey === '') {
        throw new RuntimeException('Google API key is not configured.');
    }

    $url = sprintf('https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s', urlencode($spreadsheetId), rawurlencode($range));
    $response = google_http_request($url, ['key' => $apiKey]);

    /** @var array<int, array<int, string>> $values */
    $values = $response['values'] ?? [];
    return $values;
}

/**
 * @param array<string, mixed> $config
 * @param array<int, string> $values
 */
function google_sheets_append_row(array $config, string $spreadsheetId, string $range, array $values): void
{
    if ($spreadsheetId === '') {
        throw new RuntimeException('Google Sheets spreadsheet ID is not configured.');
    }

    $apiKey = trim((string) ($config['api_key'] ?? ''));
    if ($apiKey === '') {
        throw new RuntimeException('Google API key is not configured.');
    }

    $url = sprintf('https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s:append', urlencode($spreadsheetId), rawurlencode($range));

    google_http_request(
        $url,
        [
            'key' => $apiKey,
            'valueInputOption' => 'USER_ENTERED',
            'insertDataOption' => 'INSERT_ROWS',
        ],
        [
            'values' => [$values],
        ],
        'POST'
    );
}
