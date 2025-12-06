<?php

declare(strict_types=1);

class AppLogger
{
    private bool $enabled;
    private string $filePath;
    private string $environment;

    public function __construct(bool $enabled, string $filePath, string $environment)
    {
        $this->enabled = $enabled;
        $this->filePath = $filePath;
        $this->environment = $environment;
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
            '[%s] [%s] [%s] %s',
            $timestamp,
            strtoupper($level),
            strtoupper($this->environment),
            $message
        );

        if (!empty($context)) {
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

