<?php

declare(strict_types=1);

const APP_ENV_PROD = 'prod';
const APP_ENV_TEST = 'test';

function app_public_root(?string $environment = null): string
{
    $environment = $environment ?? app_detect_environment();
    $root = dirname(__DIR__);

    if ($environment === APP_ENV_TEST) {
        return $root . '/test.thelukecenter.org';
    }

    return $root . '/public_html';
}

function app_public_path(string $file, ?string $environment = null): string
{
    $environment = $environment ?? app_detect_environment();
    $relative = ltrim($file, '/');

    $resolved = app_public_root($environment) . '/' . $relative;
    if (is_file($resolved)) {
        return $resolved;
    }

    throw new RuntimeException(sprintf('Public asset %s not found in %s root', $relative, $environment));
}

function app_htaccess_path(?string $environment = null): string
{
    $environment = $environment ?? app_detect_environment();

    $envSpecific = getenv('APP_HTACCESS_PATH_' . strtoupper($environment));
    if (is_string($envSpecific) && $envSpecific !== '') {
        return $envSpecific;
    }

    $override = getenv('APP_HTACCESS_PATH');
    if (is_string($override) && $override !== '') {
        return $override;
    }

    return dirname(app_public_root($environment)) . '/.htaccess';
}

/**
 * @return array<string,string>
 */
function app_load_htaccess_flags(bool $forceReload = false): array
{
    static $cached = null;

    if ($cached !== null && !$forceReload) {
        return $cached;
    }

    $path = app_htaccess_path();
    if (!is_file($path)) {
        $cached = [];

        return $cached;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    $flags = [];

    foreach ($lines as $line) {
        if (preg_match('/^\s*SetEnv\s+([A-Za-z_][A-Za-z0-9_]*)\s+(.+)$/i', trim($line), $matches)) {
            $value = trim($matches[2]);
            $valueParts = preg_split('/\s+#/', $value, 2);
            $flags[$matches[1]] = trim($valueParts[0]);
        }
    }

    $cached = $flags;

    return $cached;
}

/**
 * @param array<string,string|bool> $flags
 * @return array<string,string>
 */
function app_write_htaccess_flags(array $flags): array
{
    $path = app_htaccess_path();
    $existingLines = is_file($path) ? (file($path, FILE_IGNORE_NEW_LINES) ?: []) : [];

    $normalized = [];
    foreach ($flags as $key => $value) {
        $normalized[$key] = is_bool($value) ? ($value ? 'true' : 'false') : (string) $value;
    }

    $remaining = $normalized;
    $updatedLines = [];

    foreach ($existingLines as $line) {
        $updatedLine = $line;

        foreach ($normalized as $key => $value) {
            $pattern = '/^(\s*SetEnv\s+' . preg_quote($key, '/') . '\s+)(\S+)(.*)$/i';
            if (preg_match($pattern, $line, $matches)) {
                $updatedLine = $matches[1] . $value . $matches[3];
                unset($remaining[$key]);
                break;
            }
        }

        $updatedLines[] = rtrim($updatedLine, "\r\n");
    }

    foreach ($remaining as $key => $value) {
        $updatedLines[] = sprintf('SetEnv %s %s', $key, $value);
    }

    $contents = implode(PHP_EOL, $updatedLines);
    if ($contents !== '') {
        $contents .= PHP_EOL;
    }

    file_put_contents($path, $contents, LOCK_EX);

    // Reset cache
    return app_load_htaccess_flags(true);
}

function app_detect_environment(): string
{
    // Allow explicit environment selection via env vars.
    // Allow explicit environment selection for CLI contexts (e.g., cron) via env vars.
    $envOverride = getenv('APP_ENV') ?: getenv('APP_ENVIRONMENT');
    if (is_string($envOverride) && $envOverride !== '') {
        $normalized = strtolower(trim($envOverride));
        if ($normalized === APP_ENV_TEST || $normalized === APP_ENV_PROD) {
            return $normalized;
        }
    }

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
        $flags = app_load_htaccess_flags();
        if (array_key_exists($key, $flags)) {
            return (string) $flags[$key];
        }

        return $fallback;
    }

    return (string) $value;
}

function app_is_logging_enabled(): bool
{
    $raw = strtoupper(trim(app_read_env('ENABLE_APPLICATION_LOGGING')));

    return $raw === 'TRUE';
}

function app_is_developer_mode(): bool
{
    $raw = strtoupper(trim(app_read_env('DEVELOPER_MODE')));

    return in_array($raw, ['TRUE', '1', 'YES'], true);
}

function app_email_list(string $raw): array
{
    $parts = array_map('trim', explode(',', $raw));
    $filtered = array_values(array_filter($parts));

    return $filtered ?: ['contact@thelukecenter.org'];
}

function app_base_configuration(): array
{
    // Prefer environment-specific variables. Keep test configuration completely separate
    // from production so cached data and sheet selections never overlap.
    $siteValuesRange = app_read_env('GOOGLE_SITE_VALUES_RANGE', 'Values!A:B');
    $siteValuesSheetIdProd = app_read_env('GOOGLE_SITE_VALUES_SHEET_ID_PROD', app_read_env('GOOGLE_SITE_VALUES_SHEET_ID'));
    $siteValuesSheetIdTest = app_read_env('GOOGLE_SITE_VALUES_SHEET_ID_TEST', app_read_env('GOOGLE_SITE_VALUES_SHEET_ID'));
    $testimonialsRange = app_read_env('GOOGLE_SITE_TESTIMONIALS_RANGE', 'Values!A:A');

    $prodSheets = [
        'site_values_spreadsheet_id' => $siteValuesSheetIdProd,
        'site_values_range' => $siteValuesRange,
        'contact_spreadsheet_id' => app_read_env('GOOGLE_CONTACT_SHEET_ID_PROD', app_read_env('GOOGLE_CONTACT_SHEET_ID')),
        'contact_sheet_tab' => app_read_env('GOOGLE_CONTACT_SHEET_TAB', 'Submissions'),
        'application_spreadsheet_id' => app_read_env('GOOGLE_APPLICATION_SHEET_ID_PROD', app_read_env('GOOGLE_APPLICATION_SHEET_ID')),
        'application_sheet_tab' => app_read_env('GOOGLE_APPLICATION_SHEET_TAB', 'Submissions'),
        // NEW: rotating gallery Drive folder (PROD)
        'gallery_folder_id' => app_read_env('GOOGLE_GALLERY_FOLDER_ID_PROD', app_read_env('GOOGLE_GALLERY_FOLDER_ID')),
        'testimonials_spreadsheet_id' => app_read_env('GOOGLE_TESTIMONIALS_SHEET_ID_PROD', app_read_env('GOOGLE_TESTIMONIALS_SHEET_ID')),
        'testimonials_range' => $testimonialsRange,
        'developer_sheet_id' => $siteValuesSheetIdProd,
        'developer_sheet_range' => $siteValuesRange,
    ];

    $testSheets = [
        'site_values_spreadsheet_id' => $siteValuesSheetIdTest,
        'site_values_range' => $siteValuesRange,
        'contact_spreadsheet_id' => app_read_env('GOOGLE_CONTACT_SHEET_ID_TEST', app_read_env('GOOGLE_CONTACT_SHEET_ID')),
        'contact_sheet_tab' => app_read_env('GOOGLE_CONTACT_SHEET_TAB_TEST', 'Submissions'),
        'application_spreadsheet_id' => app_read_env('GOOGLE_APPLICATION_SHEET_ID_TEST', app_read_env('GOOGLE_APPLICATION_SHEET_ID')),
        'application_sheet_tab' => app_read_env('GOOGLE_APPLICATION_SHEET_TAB_TEST', 'Submissions'),
        'gallery_folder_id' => app_read_env('GOOGLE_GALLERY_FOLDER_ID_TEST', app_read_env('GOOGLE_GALLERY_FOLDER_ID')),
        'testimonials_spreadsheet_id' => app_read_env('GOOGLE_TESTIMONIALS_SHEET_ID_TEST', app_read_env('GOOGLE_TESTIMONIALS_SHEET_ID')),
        'testimonials_range' => $testimonialsRange,
        'developer_sheet_id' => $siteValuesSheetIdTest,
        'developer_sheet_range' => $siteValuesRange,
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
                'https://www.googleapis.com/auth/drive.readonly',
            ],
            'gmail_sender' => $gmailSender,
            'token_base_dir' => '/home1/bnrortmy',
            'token_subdir' => 'tokenStorage',
        ],
        'sheets' => [
            APP_ENV_PROD => $prodSheets,
            APP_ENV_TEST => $testSheets,
        ],
        'email' => [
            APP_ENV_PROD => ['recipients' => app_email_list(app_read_env('EMAIL_RECIPIENTS', 'contact@thelukecenter.org'))],
            APP_ENV_TEST => ['recipients' => app_email_list(app_read_env('EMAIL_RECIPIENTS_TEST', 'contact@thelukecenter.org'))],
        ],
        'logging' => [
            'enabled' => app_is_logging_enabled(),
            'file' => dirname(__DIR__) . '/storage/logs/application.log',
        ],
    ];
}

function app_build_configuration(string $environment): array
{
    $base = app_base_configuration();
    $sheetSet = $base['sheets'][$environment] ?? [];

    return [
        'environment' => $environment,
        'google' => array_merge($base['google'], $sheetSet),
        'email' => $base['email'][$environment] ?? ['recipients' => ['contact@thelukecenter.org']],
        'logging' => [
            'enabled' => (bool) ($base['logging']['enabled'] ?? false),
            'file' => $base['logging']['file'],
        ],
    ];
}

