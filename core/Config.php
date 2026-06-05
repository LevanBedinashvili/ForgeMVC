<?php

declare(strict_types=1);

namespace Core;

class Config
{
    /**
     * Parsed environment values.
     */
    protected static array $values = [];

    /**
     * Load and parse a .env file.
     * Skips empty lines and comments (#).
     */
    public static function load(string $path): void
    {
        if (!file_exists($path)) {
            throw new \RuntimeException(".env file not found at [{$path}].");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Skip comments
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Split on first '=' only
            $pos = strpos($line, '=');
            if ($pos === false) {
                continue;
            }

            $key = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));

            // Strip surrounding quotes if present
            if (strlen($value) >= 2 && (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            )) {
                $value = substr($value, 1, -1);
            }

            static::$values[$key] = $value;
        }
    }

    /**
     * Get an environment value by key, with an optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return static::$values[$key] ?? $default;
    }
}
