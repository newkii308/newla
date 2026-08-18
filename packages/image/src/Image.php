<?php

declare(strict_types=1);

namespace Newla\Image;

class Image
{
    public static function load(string $path): ImageProcessor
    {
        return new ImageProcessor($path);
    }

    public static function make(string $path): ImageProcessor
    {
        return static::load($path);
    }

    public static function resize(string $source, string $destination, int $width, int $height, int $quality = 85): bool
    {
        return static::load($source)->resize($width, $height)->save($destination, null, $quality);
    }

    public static function thumbnail(string $source, string $destination, int $width, int $height, int $quality = 85): bool
    {
        return static::load($source)->thumbnail($width, $height)->save($destination, null, $quality);
    }

    public static function webp(string $source, string $destination, int $quality = 85): bool
    {
        return static::load($source)->toWebp($destination, $quality);
    }

    public static function validate(string $path, int $maxSizeBytes = 5242880, array $allowedMimes = ['image/jpeg', 'image/png', 'image/webp']): bool
    {
        if (!file_exists($path) || filesize($path) > $maxSizeBytes) {
            return false;
        }

        $info = @getimagesize($path);
        if ($info === false || !in_array($info['mime'], $allowedMimes, true)) {
            return false;
        }

        return true;
    }
}