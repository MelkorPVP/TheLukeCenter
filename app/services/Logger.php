<?php

declare(strict_types=1);

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

    public function __construct(bool $enabled, string $filePath, string $environment)
    {
        $this->enabled = $enabled;
        $this->filePath = $filePath;
        $this->environment = $environment;
        $this->requestId = bin2hex(random_bytes(8));
        $this->ensureDirectory();
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
        if (!$this->enabled) {
            return;
        }

        $timestamp = (new DateTimeImmutable())->format('c');
        $line = sprintf(
            '[%s] [%s] [%s] [RID:%s] %s',
            $timestamp,
            strtoupper($level),
            strtoupper($this->environment),
            $this->requestId,
            $message
        );

        if (!empty($context)) {
            // Avoid noisy numeric keys and keep the log JSON consistent.
            $line .= ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        file_put_contents($this->filePath, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    private function ensureDirectory(): void
    {
        $dir = dirname($this->filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }
}

