<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

/**
 * Basic assertion helper for string comparisons with clearer errors.
 */
function assertSameString(string $expected, string $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message ?: sprintf("Expected string:\n%s\nGot string:\n%s", $expected, $actual));
    }
}

/**
 * Assertion helper for array comparisons.
 *
 * @param array<string,string> $expected
 * @param array<string,string> $actual
 */
function assertSameArray(array $expected, array $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message ?: sprintf('Expected array %s but got %s', json_encode($expected), json_encode($actual)));
    }
}

$htaccessPath = sys_get_temp_dir() . '/app_htaccess_flags_' . bin2hex(random_bytes(4));

putenv('APP_HTACCESS_PATH=' . $htaccessPath);

$initial = <<<'HTACCESS'
# First comment

SetEnv ENABLE_APPLICATION_LOGGING    true
# Managed application flags
   SetEnv    DEVELOPER_MODE false   # trailing comment
HTACCESS;

file_put_contents($htaccessPath, $initial . PHP_EOL);

app_load_htaccess_flags(true);

$firstResult = app_write_htaccess_flags([
    'ENABLE_APPLICATION_LOGGING' => false,
    'DEVELOPER_MODE' => true,
    'NEW_FLAG' => 'yes',
]);

$expectedFirst = <<<'HTACCESS'
# First comment

SetEnv ENABLE_APPLICATION_LOGGING    false
# Managed application flags
   SetEnv    DEVELOPER_MODE true   # trailing comment
SetEnv NEW_FLAG yes
HTACCESS;

assertSameString($expectedFirst . PHP_EOL, file_get_contents($htaccessPath), 'First write should update only values and append missing flags.');
assertSameArray([
    'ENABLE_APPLICATION_LOGGING' => 'false',
    'DEVELOPER_MODE' => 'true',
    'NEW_FLAG' => 'yes',
], $firstResult, 'First write should return normalized values.');

$secondResult = app_write_htaccess_flags([
    'ENABLE_APPLICATION_LOGGING' => true,
    'DEVELOPER_MODE' => false,
    'NEW_FLAG' => 'no',
]);

$expectedSecond = <<<'HTACCESS'
# First comment

SetEnv ENABLE_APPLICATION_LOGGING    true
# Managed application flags
   SetEnv    DEVELOPER_MODE false   # trailing comment
SetEnv NEW_FLAG no
HTACCESS;

assertSameString($expectedSecond . PHP_EOL, file_get_contents($htaccessPath), 'Second write should keep original spacing and order.');
assertSameArray([
    'ENABLE_APPLICATION_LOGGING' => 'true',
    'DEVELOPER_MODE' => 'false',
    'NEW_FLAG' => 'no',
], $secondResult, 'Second write should return updated normalized values.');

unlink($htaccessPath);
