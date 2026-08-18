<?php

declare(strict_types=1);

namespace Newla\Core\Config;

use Newla\Core\Support\Arr;

class Repository
{
    protected array $items = [];

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function loadFromDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        foreach (glob(rtrim($path, '/\\') . '/*.php') as $file) {
            $key = basename($file, '.php');
            $this->items[$key] = require $file;
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->items, $key, $default);
    }

    public function set(string $key, mixed $value): void
    {
        Arr::set($this->items, $key, $value);
    }

    public function has(string $key): bool
    {
        return Arr::has($this->items, $key);
    }

    public function all(): array
    {
        return $this->items;
    }
}