<?php

declare(strict_types=1);

namespace Newla\Core\Support;

class Path
{
    public static function normalize(string $path): string
    {
        return str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);
    }

    public static function join(string ...$paths): string
    {
        $normalized = array_map(fn($p) => trim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $p), DIRECTORY_SEPARATOR), $paths);
        $first = $paths[0] ?? '';
        $isAbsolute = str_starts_with($first, '/') || str_starts_with($first, '\\') || (strlen($first) > 1 && $first[1] === ':');
        
        $result = implode(DIRECTORY_SEPARATOR, array_filter($normalized, fn($p) => $p !== ''));
        
        if ($isAbsolute && !str_starts_with($result, DIRECTORY_SEPARATOR) && (!isset($result[1]) || $result[1] !== ':')) {
            $result = DIRECTORY_SEPARATOR . $result;
        }

        return $result;
    }
}