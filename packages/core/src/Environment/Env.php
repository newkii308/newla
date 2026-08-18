<?php

declare(strict_types=1);

namespace Newla\Core\Environment;

class Env
{
    protected static array $variables = [];

    public static function load(string $directory, string $file = '.env'): void
    {
        $filePath = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . $file;
        if (!file_exists($filePath)) {
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Strip outer quotes if any
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }

            static::set($key, $value);
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, static::$variables)) {
            return static::$variables[$key];
        }

        $value = getenv($key);
        if ($value !== false) {
            return static::parseValue($value);
        }

        if (isset($_ENV[$key])) {
            return static::parseValue($_ENV[$key]);
        }

        if (isset($_SERVER[$key])) {
            return static::parseValue($_SERVER[$key]);
        }

        return $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $parsed = is_string($value) ? static::parseValue($value) : $value;
        static::$variables[$key] = $parsed;
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv("{$key}={$value}");
    }

    protected static function parseValue(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $lower = strtolower($value);
        if ($lower === 'true' || $lower === '(true)') {
            return true;
        }
        if ($lower === 'false' || $lower === '(false)') {
            return false;
        }
        if ($lower === 'null' || $lower === '(null)') {
            return null;
        }
        if ($lower === 'empty' || $lower === '(empty)') {
            return '';
        }
        if (is_numeric($value)) {
            return str_contains($value, '.') ? (float) $value : (int) $value;
        }

        return $value;
    }

    public static function all(): array
    {
        return static::$variables;
    }
}