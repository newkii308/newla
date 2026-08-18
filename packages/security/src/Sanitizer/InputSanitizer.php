<?php

declare(strict_types=1);

namespace Newla\Security\Sanitizer;

class InputSanitizer
{
    public static function clean(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map([static::class, 'clean'], $value);
        }

        if (!is_string($value)) {
            return $value;
        }

        // Strip null bytes and control characters
        $value = str_replace(["\0", "\x0B"], '', $value);
        return trim($value);
    }

    public static function escapeHtml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function safeFilename(string $filename): string
    {
        $filename = basename($filename);
        $filename = preg_replace('/[^\w\-\.]/', '_', $filename);
        return ltrim($filename, '.');
    }

    public static function cleanEmail(string $email): string
    {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL) ?: '';
    }

    public static function cleanUrl(string $url): string
    {
        return filter_var(trim($url), FILTER_SANITIZE_URL) ?: '';
    }
}