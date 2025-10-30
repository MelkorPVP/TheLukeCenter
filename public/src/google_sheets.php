<?php

declare(strict_types=1);

require_once __DIR__ . '/google_auth.php';

/**
 * @param array<string, mixed> $config
 * @return array<int, array<int, string>>
 */
function google_sheets_get_values(array $config, string $spreadsheetId, string $range): array
{
    if ($spreadsheetId === '') {
        throw new RuntimeException('Google Sheets spreadsheet ID is not configured.');
    }

    $token = google_service_account_token($config, ['https://www.googleapis.com/auth/spreadsheets.readonly']);
    $url = sprintf('https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s', urlencode($spreadsheetId), rawurlencode($range));
    $response = google_http_request($url, [], $token, 'GET');

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

    $token = google_service_account_token($config, ['https://www.googleapis.com/auth/spreadsheets']);
    $url = sprintf(
        'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s:append?valueInputOption=USER_ENTERED&insertDataOption=INSERT_ROWS',
        urlencode($spreadsheetId),
        rawurlencode($range)
    );

    google_http_request(
        $url,
        [
            'values' => [$values],
        ],
        $token,
        'POST'
    );
}
