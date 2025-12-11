<?php

declare(strict_types=1);

/**
 * Commenting convention:
 * - Docblocks summarize function intent along with key inputs/outputs.
 * - Inline context comments precede major initialization, configuration, or external calls.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/GoogleService.php';
require_once __DIR__ . '/ContentService.php';
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
    $credentials = [];
    $values = site_content_values($config, $logger);

    foreach (['developer_mode_username', 'developer_mode_password'] as $credentialKey) {
        if (array_key_exists($credentialKey, $values)) {
            $credentials[$credentialKey] = $values[$credentialKey];
        }
    }

    return $credentials;
}

/**
 * @param array<string,mixed> $config
 */
function developer_validate_credentials(array $config, string $username, string $password, ?AppLogger $logger = null): bool
{
    try {
        $payload = site_content_resolve_payload($config, $logger);
    } catch (Throwable $e) {
        $payload = [];

        if ($logger instanceof AppLogger && $logger->isEnabled()) {
            $logger->error('Developer credential payload lookup failed', [
                'exception' => get_class($e),
            ]);
        }
    }

    // Pull credentials from the Google Sheet so operators can rotate them without code changes.
    try {
        $credentials = developer_fetch_sheet_credentials($config, $logger);
    } catch (Throwable $e) {
        if ($logger instanceof AppLogger && $logger->isEnabled()) {
            $logger->error('Developer credentials lookup failed', [
                'exception' => get_class($e),
            ]);
        }

        return false;
    }
    $expectedUser = trim((string) ($credentials['developer_mode_username'] ?? ''));
    $expectedPassword = (string) ($credentials['developer_mode_password'] ?? '');

    if ($expectedUser === '' || $expectedPassword === '') {
        if ($logger instanceof AppLogger && $logger->isEnabled()) {
            $logger->error('Developer credentials are missing in the sheet.');
        }

        return false;
    }

    // Prefer hashed payload values, falling back to hashing plaintext sheet entries for backward compatibility.
    $expectedUserHash = trim((string) ($payload['developer_mode_username_hash'] ?? ''));
    $expectedPasswordHash = trim((string) ($payload['developer_mode_password_hash'] ?? ''));
    $sheetUserHash = developer_hash_password($expectedUser);
    $sheetPasswordHash = developer_hash_password($expectedPassword);

    if (($expectedUserHash === '' || $expectedPasswordHash === '') && $logger instanceof AppLogger && $logger->isEnabled()) {
        $logger->error('Developer credential hash missing from payload', [
            'missing_username_hash' => $expectedUserHash === '',
            'missing_password_hash' => $expectedPasswordHash === '',
        ]);
    }

    if ($expectedUserHash !== '' && !hash_equals($expectedUserHash, $sheetUserHash) && $logger instanceof AppLogger && $logger->isEnabled()) {
        $logger->error('Developer username hash mismatches plaintext value');
    }

    if ($expectedPasswordHash !== '' && !hash_equals($expectedPasswordHash, $sheetPasswordHash) && $logger instanceof AppLogger && $logger->isEnabled()) {
        $logger->error('Developer password hash mismatches plaintext value');
    }

    $expectedUserHash = $expectedUserHash !== '' ? $expectedUserHash : $sheetUserHash;
    $expectedPasswordHash = $expectedPasswordHash !== '' ? $expectedPasswordHash : $sheetPasswordHash;
    $suppliedUserHash = developer_hash_password($username);
    $suppliedPasswordHash = developer_hash_password($password);

    return hash_equals($expectedUserHash, $suppliedUserHash)
        && hash_equals($expectedPasswordHash, $suppliedPasswordHash);
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
 * Recursively copy all files and directories from one root to another.
 *
 * @return array<int,string> list of relative paths copied
 */
function developer_recursive_copy_directory(string $sourceDir, string $destinationDir): array
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

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
        $files[] = $relativePath;
    }

    return $files;
}

/**
 * Remove a directory and all of its contents.
 */
function developer_clear_directory(string $targetDir): int
{
    if (!is_dir($targetDir)) {
        return 0;
    }

    $removed = 0;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($targetDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $item) {
        /** @var SplFileInfo $item */
        if ($item->isDir()) {
            rmdir($item->getPathname());
        } else {
            unlink($item->getPathname());
        }
        $removed++;
    }

    rmdir($targetDir);

    return $removed + 1; // include the root directory removal
}

/**
 * @return array{copied:int,files:array<int,string>,backup_path:string}
 */
function developer_copy_overlay(string $sourceEnv, string $destinationEnv, ?AppLogger $logger = null): array
{
    $sourceDir = app_public_root($sourceEnv);
    $destinationDir = app_public_root($destinationEnv);

    if (!is_dir($destinationDir)) {
        mkdir($destinationDir, 0755, true);
    }

    $timestamp = (new DateTimeImmutable())->format('YmdHis');
    $backupRoot = (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/backups';
    $backupDir = $backupRoot . DIRECTORY_SEPARATOR . $destinationEnv . '-' . $timestamp;

    if (!is_dir($backupRoot)) {
        mkdir($backupRoot, 0755, true);
    }

    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }

    $backedUpFiles = developer_recursive_copy_directory($destinationDir, $backupDir);

    if ($logger instanceof AppLogger && $logger->isEnabled()) {
        $logger->info('Destination snapshot created', [
            'environment' => $destinationEnv,
            'destination_root' => $destinationDir,
            'backup_path' => $backupDir,
            'backed_up_files' => $backedUpFiles,
            'backed_up_count' => count($backedUpFiles),
        ]);
    }

    if (!is_dir($sourceDir)) {
        throw new RuntimeException(sprintf('Source site root %s is missing', $sourceDir));
    }

    $removedEntries = developer_clear_directory($destinationDir);

    if (!is_dir($destinationDir)) {
        mkdir($destinationDir, 0755, true);
    }

    if ($logger instanceof AppLogger && $logger->isEnabled()) {
        $logger->info('Destination directory reset', [
            'environment' => $destinationEnv,
            'destination_root' => $destinationDir,
            'removed_entries' => $removedEntries,
        ]);
    }

    $files = developer_recursive_copy_directory($sourceDir, $destinationDir);
    $copied = count($files);

    if ($logger instanceof AppLogger && $logger->isEnabled()) {
        $logger->info('Site sync completed', [
            'source' => $sourceEnv,
            'destination' => $destinationEnv,
            'source_root' => $sourceDir,
            'destination_root' => $destinationDir,
            'files' => $files,
            'backup_path' => $backupDir,
        ]);
    }

    return ['copied' => $copied, 'files' => $files, 'backup_path' => $backupDir];
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
