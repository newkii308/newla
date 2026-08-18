<?php

declare(strict_types=1);

namespace Newla\Core\Support;

class Str
{
    public static function startsWith(string $haystack, string|array $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && str_starts_with($haystack, $needle)) {
                return true;
            }
        }
        return false;
    }

    public static function endsWith(string $haystack, string|array $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && str_ends_with($haystack, $needle)) {
                return true;
            }
        }
        return false;
    }

    public static function contains(string $haystack, string|array $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && str_contains($haystack, $needle)) {
                return true;
            }
        }
        return false;
    }

    public static function camel(string $value): string
    {
        return lcfirst(static::studly($value));
    }

    public static function studly(string $value): string
    {
        $words = explode(' ', str_replace(['-', '_'], ' ', $value));
        $studlyWords = array_map(fn($word) => ucfirst($word), $words);
        return implode('', $studlyWords);
    }

    public static function snake(string $value, string $delimiter = '_'): string
    {
        $key = $value;
        if (!ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));
            $value = preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value);
            return mb_strtolower($value, 'UTF-8');
        }
        return $value;
    }

    public static function random(int $length = 16): string
    {
        $bytes = random_bytes((int) ceil($length / 2));
        return substr(bin2hex($bytes), 0, $length);
    }

    public static function plural(string $value): string
    {
        if (str_ends_with($value, 'y') && !preg_match('/[aeiou]y$/i', $value)) {
            return substr($value, 0, -1) . 'ies';
        }
        if (str_ends_with($value, 's') || str_ends_with($value, 'x') || str_ends_with($value, 'ch') || str_ends_with($value, 'sh')) {
            return $value . 'es';
        }
        return $value . 's';
    }

    public static function slug(string $title, string $separator = '-'): string
    {
        $title = mb_strtolower(trim($title), 'UTF-8');
        $title = preg_replace('/[^\p{L}\p{N}]+/u', $separator, $title);
        return trim($title, $separator);
    }
}