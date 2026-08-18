<?php

declare(strict_types=1);

namespace Newla\Storage;

use InvalidArgumentException;
use Newla\Storage\Driver\LocalStorageDriver;
use Newla\Storage\Driver\S3StorageDriver;
use Newla\Storage\Driver\StorageDriverInterface;

class Storage
{
    protected static array $disks = [];
    protected static ?string $defaultDisk = null;

    public static function disk(?string $name = null): StorageDriverInterface
    {
        $name = $name ?? static::getDefaultDriver();

        if (!isset(static::$disks[$name])) {
            static::$disks[$name] = static::createDriver($name);
        }

        return static::$disks[$name];
    }

    public static function getDefaultDriver(): string
    {
        return function_exists('config') ? config('storage.default', 'local') : 'local';
    }

    protected static function createDriver(string $name): StorageDriverInterface
    {
        $config = function_exists('config') ? config("storage.disks.{$name}", []) : [];
        $driver = $config['driver'] ?? $name;

        return match ($driver) {
            'local' => new LocalStorageDriver(
                $config['root'] ?? (function_exists('storage_path') ? storage_path('uploads') : getcwd() . '/storage/uploads'),
                $config['url'] ?? '/storage'
            ),
            's3', 'r2' => new S3StorageDriver($config),
            default => throw new InvalidArgumentException("Unsupported storage driver [{$driver}]."),
        };
    }

    public static function put(string $path, string $contents, array $options = []): bool
    {
        return static::disk()->put($path, $contents, $options);
    }

    public static function get(string $path): ?string
    {
        return static::disk()->get($path);
    }

    public static function delete(string $path): bool
    {
        return static::disk()->delete($path);
    }

    public static function exists(string $path): bool
    {
        return static::disk()->exists($path);
    }

    public static function size(string $path): int
    {
        return static::disk()->size($path);
    }

    public static function url(string $path): string
    {
        return static::disk()->url($path);
    }

    public static function files(string $directory = ''): array
    {
        return static::disk()->files($directory);
    }
}