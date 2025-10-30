<?php

declare(strict_types=1);

namespace TheLukeCenter\Support;

final class Config
{
    /**
     * @param string $path
     *
     * @return array<string, mixed>
     */
    public static function load(string $path): array
    {
        if (!is_file($path)) {
            throw new \RuntimeException(sprintf('Config file not found: %s', $path));
        }

        /** @var array<string, mixed> $config */
        $config = require $path;

        if (!is_array($config)) {
            throw new \RuntimeException('Config file must return an array.');
        }

        return $config;
    }
}
