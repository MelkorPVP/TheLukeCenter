<?php

declare(strict_types=1);

const APP_ENV_PROD = 'prod';
const APP_ENV_TEST = 'test';

function app_detect_environment(): string
{
    $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
    if (strpos($host, 'test.thelukecenter.org') !== false) {
        return APP_ENV_TEST;
    }

    return APP_ENV_PROD;
}

function app_read_env(string $key, string $fallback = ''): string
{
    $value = getenv($key);
    if ($value === false) {
        return $fallback;
    }

    return (string) $value;
}

function app_email_list(string $raw): array
{
    $parts = array_map('trim', explode(',', $raw));
    $filtered = array_values(array_filter($parts));

    return $filtered ?: ['contact@thelukecenter.org'];
}

function app_base_configuration(): array
{
    $prodSheets = [
        'site_values_spreadsheet_id' => app_read_env('GOOGLE_SITE_VALUES_SHEET_ID'),
        'site_values_range' => app_read_env('GOOGLE_SITE_VALUES_RANGE', 'Values!A:B'),
        'contact_spreadsheet_id' => app_read_env('GOOGLE_CONTACT_SHEET_ID'),
        'contact_sheet_tab' => app_read_env('GOOGLE_CONTACT_SHEET_TAB', 'Submissions'),
        'application_spreadsheet_id' => app_read_env('GOOGLE_APPLICATION_SHEET_ID'),
        'application_sheet_tab' => app_read_env('GOOGLE_APPLICATION_SHEET_TAB', 'Submissions'),
    ];

    $testSheets = [
        'site_values_spreadsheet_id' => app_read_env('GOOGLE_SITE_VALUES_SHEET_ID_TEST', $prodSheets['site_values_spreadsheet_id']),
        'site_values_range' => app_read_env('GOOGLE_SITE_VALUES_RANGE_TEST', $prodSheets['site_values_range']),
        'contact_spreadsheet_id' => app_read_env('GOOGLE_CONTACT_SHEET_ID_TEST', $prodSheets['contact_spreadsheet_id']),
        'contact_sheet_tab' => app_read_env('GOOGLE_CONTACT_SHEET_TAB_TEST', $prodSheets['contact_sheet_tab']),
        'application_spreadsheet_id' => app_read_env('GOOGLE_APPLICATION_SHEET_ID_TEST', $prodSheets['application_spreadsheet_id']),
        'application_sheet_tab' => app_read_env('GOOGLE_APPLICATION_SHEET_TAB_TEST', $prodSheets['application_sheet_tab']),
    ];

    $gmailSender = app_read_env('GOOGLE_GMAIL_SENDER', 'contact@thelukecenter.org');

    return [
        'google' => [
            'oauth_client_id' => app_read_env('GOOGLE_OAUTH_CLIENT_ID'),
            'oauth_client_secret' => app_read_env('GOOGLE_OAUTH_CLIENT_SECRET'),
            'oauth_redirect_uri' => app_read_env('GOOGLE_OAUTH_REDIRECT_URI'),
            'oauth_scopes' => [
                'https://www.googleapis.com/auth/spreadsheets',
                'https://www.googleapis.com/auth/gmail.send',
            ],
            'gmail_sender' => $gmailSender,
            'token_base_dir' => '/home3/bnrortmy',
            'token_subdir' => 'tokenStorage',
        ],
        'sheets' => [
            APP_ENV_PROD => $prodSheets,
            APP_ENV_TEST => $testSheets,
        ],
        'email' => [
            APP_ENV_PROD => ['recipients' => app_email_list(app_read_env('EMAIL_RECIPIENTS', 'contact@thelukecenter.org'))],
            APP_ENV_TEST => ['recipients' => app_email_list(app_read_env('EMAIL_RECIPIENTS_TEST', app_read_env('EMAIL_RECIPIENTS', 'contact@thelukecenter.org')))],
        ],
        'logging' => [
            'default' => [
                APP_ENV_PROD => false,
                APP_ENV_TEST => true,
            ],
            'file' => dirname(__DIR__) . '/storage/logs/application.log',
        ],
    ];
}

function app_build_configuration(string $environment): array
{
    $base = app_base_configuration();
    $sheetSet = $base['sheets'][$environment] ?? $base['sheets'][APP_ENV_PROD];

    return [
        'environment' => $environment,
        'google' => array_merge($base['google'], $sheetSet),
        'email' => $base['email'][$environment] ?? $base['email'][APP_ENV_PROD],
        'logging' => [
            'enabled' => (bool) ($base['logging']['default'][$environment] ?? false),
            'file' => $base['logging']['file'],
        ],
    ];
}

function app_logging_override(array $config, ?callable $contentResolver = null): bool
{
    $default = (bool) ($config['logging']['enabled'] ?? false);
    if ($contentResolver === null) {
        return $default;
    }

    try {
        $values = $contentResolver();
        $raw = strtoupper(trim((string) ($values['logging_enabled'] ?? '')));
        if ($raw === '') {
            return $default;
        }

        return in_array($raw, ['1', 'TRUE', 'YES', 'ON', 'ENABLED'], true);
    } catch (Throwable $e) {
        return $default;
    }
}

