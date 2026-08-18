<?php

declare(strict_types=1);

namespace Newla\Storage\Driver;

class LocalStorageDriver implements StorageDriverInterface
{
    protected string $root;
    protected string $urlBase;

    public function __construct(string $root, string $urlBase = '')
    {
        $this->root = rtrim($root, '/\\');
        $this->urlBase = rtrim($urlBase, '/');
        if (!is_dir($this->root)) {
            @mkdir($this->root, 0777, true);
        }
    }

    public function normalizeRelativePath(string $path): string
    {
        $path = str_replace(['\\'], '/', $path);
        $parts = [];
        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..' || str_contains($segment, '..')) {
                throw new \InvalidArgumentException("Invalid path: path traversal detected in [{$path}]");
            }
            $parts[] = $segment;
        }
        return implode(DIRECTORY_SEPARATOR, $parts);
    }

    public function getFullPath(string $path): string
    {
        $relative = $this->normalizeRelativePath($path);
        return $relative !== '' ? $this->root . DIRECTORY_SEPARATOR . $relative : $this->root;
    }

    public function put(string $path, string $contents, array $options = []): bool
    {
        $fullPath = $this->getFullPath($path);
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        return file_put_contents($fullPath, $contents, LOCK_EX) !== false;
    }

    public function get(string $path): ?string
    {
        $fullPath = $this->getFullPath($path);
        if (!file_exists($fullPath)) {
            return null;
        }

        $content = file_get_contents($fullPath);
        return $content !== false ? $content : null;
    }

    public function delete(string $path): bool
    {
        $fullPath = $this->getFullPath($path);
        if (file_exists($fullPath)) {
            return @unlink($fullPath);
        }
        return true;
    }

    public function exists(string $path): bool
    {
        return file_exists($this->getFullPath($path));
    }

    public function size(string $path): int
    {
        $fullPath = $this->getFullPath($path);
        return file_exists($fullPath) ? (int) filesize($fullPath) : 0;
    }

    public function lastModified(string $path): int
    {
        $fullPath = $this->getFullPath($path);
        return file_exists($fullPath) ? (int) filemtime($fullPath) : 0;
    }

    public function mimeType(string $path): ?string
    {
        $fullPath = $this->getFullPath($path);
        if (!file_exists($fullPath)) {
            return null;
        }
        return mime_content_type($fullPath) ?: 'application/octet-stream';
    }

    public function url(string $path): string
    {
        $cleanPath = ltrim(str_replace('\\', '/', $path), '/');
        if (empty($this->urlBase)) {
            return '/' . $cleanPath;
        }
        return $this->urlBase . '/' . $cleanPath;
    }

    public function files(string $directory = ''): array
    {
        $target = $this->getFullPath($directory);
        if (!is_dir($target)) {
            return [];
        }

        $items = scandir($target);
        $files = [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $full = $target . DIRECTORY_SEPARATOR . $item;
            if (is_file($full)) {
                $rel = ltrim($directory . '/' . $item, '/');
                $files[] = $rel;
            }
        }

        return $files;
    }

    public function makeDirectory(string $path): bool
    {
        $full = $this->getFullPath($path);
        if (is_dir($full)) {
            return true;
        }
        return @mkdir($full, 0777, true);
    }

    public function deleteDirectory(string $path): bool
    {
        $full = $this->getFullPath($path);
        if (!is_dir($full)) {
            return true;
        }

        $files = array_diff(scandir($full), ['.', '..']);
        foreach ($files as $file) {
            $sub = $full . DIRECTORY_SEPARATOR . $file;
            is_dir($sub) ? $this->deleteDirectory($path . '/' . $file) : @unlink($sub);
        }

        return @rmdir($full);
    }
}