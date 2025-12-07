<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/GoogleService.php';
require_once __DIR__ . '/Logger.php';

const APP_DEVELOPER_SESSION_KEY = 'developer_authenticated';
const APP_DEVELOPER_USERNAME_KEY = 'developer_username';

function developer_is_authenticated(): bool
{
    return ($_SESSION[APP_DEVELOPER_SESSION_KEY] ?? false) === true;
}

function developer_require_auth(): void
{
    if (developer_is_authenticated()) {
        return;
    }

    http_response_code(403);
    exit('Developer authentication required.');
}

function developer_hash_password(string $password): string
{
    return hash('sha256', $password);
}

/**
 * @param array<string,mixed> $config
 * @return array<string,string>
 */
function developer_fetch_sheet_credentials(array $config, ?AppLogger $logger = null): array
{
    $googleConfig = $config['google'] ?? [];
    $sheetId = (string) ($config['google']['developer_sheet_id'] ?? '');
    $range = (string) ($config['google']['developer_sheet_range'] ?? 'Developer!A:B');

    if ($sheetId === '') {
        throw new RuntimeException('Developer credentials sheet is not configured.');
    }

    $values = google_sheets_get_values(
        $googleConfig,
        [
            'spreadsheet_id' => $sheetId,
            'range' => $range,
        ],
        null,
        $logger
    );

    $mapped = [];
    foreach ($values as $row) {
        if (!isset($row[0]) || !isset($row[1])) {
            continue;
        }

        $key = trim((string) $row[0]);
        if ($key === '') {
            continue;
        }

        $mapped[$key] = trim((string) $row[1]);
    }

    return $mapped;
}

/**
 * @param array<string,mixed> $config
 */
function developer_validate_credentials(array $config, string $username, string $password, ?AppLogger $logger = null): bool
{
    $credentials = developer_fetch_sheet_credentials($config, $logger);
    $expectedUser = $credentials['DeveloperModeUsername'] ?? '';
    $expectedPassword = $credentials['DeveloperModePassword'] ?? '';

    if ($expectedUser === '' || $expectedPassword === '') {
        throw new RuntimeException('Developer credentials are missing in the sheet.');
    }

    $suppliedHash = developer_hash_password($password);

    return hash_equals($expectedUser, $username) && hash_equals($expectedPassword, $suppliedHash);
}

function developer_start_session(string $username): void
{
    $_SESSION[APP_DEVELOPER_SESSION_KEY] = true;
    $_SESSION[APP_DEVELOPER_USERNAME_KEY] = $username;
}

function developer_end_session(): void
{
    unset($_SESSION[APP_DEVELOPER_SESSION_KEY], $_SESSION[APP_DEVELOPER_USERNAME_KEY]);
}

/**
 * @return array<string,string>
 */
function developer_set_env_flags(array $flags): array
{
    return app_write_htaccess_flags($flags);
}

/**
 * @return array<string,string>
 */
function developer_current_env_flags(): array
{
    return app_load_htaccess_flags();
}

/**
 * @return array{copied:int,files:array<int,string>}
 */
function developer_copy_overlay(string $sourceEnv, string $destinationEnv, ?AppLogger $logger = null): array
{
    $sourceDir = app_public_overlay_dir($sourceEnv);
    $destinationDir = app_public_overlay_dir($destinationEnv);

    if (!is_dir($sourceDir)) {
        throw new RuntimeException(sprintf('Source overlay %s is missing', $sourceDir));
    }

    if (!is_dir($destinationDir)) {
        mkdir($destinationDir, 0755, true);
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    $copied = 0;
    $files = [];

    foreach ($iterator as $item) {
        /** @var SplFileInfo $item */
        $relativePath = substr($item->getPathname(), strlen($sourceDir) + 1);
        $targetPath = $destinationDir . DIRECTORY_SEPARATOR . $relativePath;

        if ($item->isDir()) {
            if (!is_dir($targetPath)) {
                mkdir($targetPath, 0755, true);
            }
            continue;
        }

        if (!is_dir(dirname($targetPath))) {
            mkdir(dirname($targetPath), 0755, true);
        }

        copy($item->getPathname(), $targetPath);
        $copied++;
        $files[] = $relativePath;
    }

    if ($logger instanceof AppLogger && $logger->isEnabled()) {
        $logger->info('Overlay sync completed', [
            'source' => $sourceEnv,
            'destination' => $destinationEnv,
            'files' => $files,
        ]);
    }

    return ['copied' => $copied, 'files' => $files];
}

/**
 * Remove log entries older than $daysBack days from the application log file.
 *
 * @return array{remaining:int,removed:int}
 */
function developer_purge_logs(string $logPath, int $daysBack = 30, ?AppLogger $logger = null): array
{
    if (!is_file($logPath)) {
        return ['remaining' => 0, 'removed' => 0];
    }

    $cutoff = (new DateTimeImmutable(sprintf('-%d days', $daysBack)))->getTimestamp();
    $lines = file($logPath, FILE_IGNORE_NEW_LINES) ?: [];
    $kept = [];
    $removed = 0;

    foreach ($lines as $line) {
        if (preg_match('/^\[(.+?)\]/', $line, $matches)) {
            $timestamp = strtotime($matches[1]);
            if ($timestamp !== false && $timestamp < $cutoff) {
                $removed++;
                continue;
            }
        }

        $kept[] = $line;
    }

    file_put_contents($logPath, implode(PHP_EOL, $kept) . (empty($kept) ? '' : PHP_EOL), LOCK_EX);

    $remaining = count($kept);

    if ($logger instanceof AppLogger && $logger->isEnabled()) {
        $logger->info('Purged application log entries', [
            'removed' => $removed,
            'remaining' => $remaining,
        ]);
    }

    return ['remaining' => $remaining, 'removed' => $removed];
}
