<?php

declare(strict_types=1);

namespace Newla\Core\Config;

use Newla\Core\Container\Container;

class Config
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return Container::getInstance()->make('config')->get($key, $default);
    }

    public static function set(string $key, mixed $value): void
    {
        Container::getInstance()->make('config')->set($key, $value);
    }

    public static function has(string $key): bool
    {
        return Container::getInstance()->make('config')->has($key);
    }

    public static function all(): array
    {
        return Container::getInstance()->make('config')->all();
    }
}