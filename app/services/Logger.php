<?php

declare(strict_types=1);

/**
 * Commenting convention:
 * - Docblocks summarize function intent along with key inputs/outputs.
 * - Inline context comments precede major initialization, configuration, or external calls.
 */

class AppLogger
{
    /**
     * Whether logging is active.
     */
    private bool $enabled;

    /**
     * Full path to the log file on disk.
     */
    private string $filePath;

    /**
     * Current environment tag (prod/test) to prefix log lines.
     */
    private string $environment;

    /**
     * Per-request identifier to correlate log messages across the request lifecycle.
     */
    private string $requestId;

    /**
     * Callable responsible for writing a formatted log line to the destination.
     *
     * @var callable(string):void
     */
    private $writer;

    /**
     * Callable responsible for formatting the log line before writing.
     *
     * @var callable(string,string,string,string,array<string,mixed>,string):string
     */
    private $formatter;

    public function __construct(bool $enabled, string $filePath, string $environment, ?callable $writer = null, ?callable $formatter = null)
    {
        $this->enabled = $enabled;
        $this->filePath = $filePath;
        $this->environment = $environment;
        $this->requestId = bin2hex(random_bytes(8));
        $this->ensureDirectory();
        $this->writer = $writer ?? $this->buildDefaultWriter();
        $this->formatter = $formatter ?? [$this, 'formatLine'];
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Expose the generated request identifier so application code can include
     * it in headers or debugging output when necessary.
     */
    public function getRequestId(): string
    {
        return $this->requestId;
    }

    public function info(string $message, array $context = []): void
    {
        $this->write('INFO', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->write('ERROR', $message, $context);
    }

    public function write(string $level, string $message, array $context = []): void
    {
        // Respect configuration-driven disablement before constructing log payloads.
        if (!$this->enabled) {
            return;
        }

        $timestamp = (new DateTimeImmutable())->format('c');
        $caller = $this->resolveCallerLocation();

        $line = call_user_func($this->formatter, $timestamp, strtoupper($level), strtoupper($this->environment), $caller, $context, $message);

        call_user_func($this->writer, $line . PHP_EOL);
    }

    private function ensureDirectory(): void
    {
        $dir = dirname($this->filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }

    /**
     * Locate the first non-logger frame in the backtrace.
     */
    private function resolveCallerLocation(): string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 8);

        foreach ($trace as $frame) {
            $file = $frame['file'] ?? null;
            $line = $frame['line'] ?? null;

            if ($file === null || $file === __FILE__) {
                continue;
            }

            return sprintf('%s:%s', $file, $line ?? '?');
        }

        return 'unknown:0';
    }

    private function buildDefaultWriter(): callable
    {
        $filePath = $this->filePath;

        return static function (string $line) use ($filePath): void {
            file_put_contents($filePath, $line, FILE_APPEND | LOCK_EX);
        };
    }

    private function formatLine(string $timestamp, string $level, string $environment, string $caller, array $context, string $message): string
    {
        $line = sprintf('[%s] [%s] [%s] [RID:%s] [CALLER:%s] %s', $timestamp, $level, $environment, $this->requestId, $caller, $message);

        if (!empty($context)) {
            $line .= ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return $line;
    }
}

/**
 * Build a reusable log writer pointing at the configured log file.
 */
function app_logger_file_writer(string $filePath): callable
{
    $dir = dirname($filePath);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    return static function (string $line) use ($filePath): void {
        file_put_contents($filePath, $line, FILE_APPEND | LOCK_EX);
    };
}

/**
 * Construct a logger based on the shared configuration array.
 *
 * @param array{enabled?:bool,file?:string} $loggingConfig
 */
function app_logger_from_config(array $loggingConfig, string $environment, ?callable $writer = null, ?callable $formatter = null): AppLogger
{
    $defaultRoot = defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 1);
    $filePath = $loggingConfig['file'] ?? ($defaultRoot . '/storage/logs/application.log');

    return new AppLogger(
        (bool) ($loggingConfig['enabled'] ?? false),
        $filePath,
        $environment,
        $writer ?? app_logger_file_writer($filePath),
        $formatter
    );
}

