<?php

declare(strict_types=1);

namespace Newla\Storage\Driver;

interface StorageDriverInterface
{
    public function put(string $path, string $contents, array $options = []): bool;
    public function get(string $path): ?string;
    public function delete(string $path): bool;
    public function exists(string $path): bool;
    public function size(string $path): int;
    public function lastModified(string $path): int;
    public function mimeType(string $path): ?string;
    public function url(string $path): string;
    public function files(string $directory = ''): array;
    public function makeDirectory(string $path): bool;
    public function deleteDirectory(string $path): bool;
}