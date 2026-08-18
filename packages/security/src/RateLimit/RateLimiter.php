<?php

declare(strict_types=1);

namespace Newla\Security\RateLimit;

class RateLimiter
{
    protected string $storagePath;

    public function __construct(?string $storagePath = null)
    {
        $this->storagePath = $storagePath ?? (function_exists('storage_path') ? storage_path('cache/ratelimit') : sys_get_temp_dir() . '/newla_ratelimit');
        if (!is_dir($this->storagePath)) {
            @mkdir($this->storagePath, 0777, true);
        }
    }

    public function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        return $this->attempts($key) >= $maxAttempts;
    }

    public function hit(string $key, int $decaySeconds = 60): int
    {
        $file = $this->getFile($key);
        $now = time();
        $data = ['count' => 0, 'expires_at' => $now + $decaySeconds];

        if (file_exists($file)) {
            $raw = file_get_contents($file);
            $parsed = @json_decode($raw, true);
            if (is_array($parsed) && ($parsed['expires_at'] ?? 0) > $now) {
                $data = $parsed;
            }
        }

        $data['count']++;
        file_put_contents($file, json_encode($data), LOCK_EX);

        return $data['count'];
    }

    public function attempts(string $key): int
    {
        $file = $this->getFile($key);
        if (!file_exists($file)) {
            return 0;
        }

        $raw = file_get_contents($file);
        $data = @json_decode($raw, true);

        if (!is_array($data) || ($data['expires_at'] ?? 0) <= time()) {
            @unlink($file);
            return 0;
        }

        return (int) ($data['count'] ?? 0);
    }

    public function remaining(string $key, int $maxAttempts): int
    {
        return max(0, $maxAttempts - $this->attempts($key));
    }

    public function availableIn(string $key): int
    {
        $file = $this->getFile($key);
        if (!file_exists($file)) {
            return 0;
        }

        $data = @json_decode(file_get_contents($file), true);
        if (!is_array($data)) {
            return 0;
        }

        return max(0, ($data['expires_at'] ?? time()) - time());
    }

    public function clear(string $key): void
    {
        $file = $this->getFile($key);
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    protected function getFile(string $key): string
    {
        return $this->storagePath . DIRECTORY_SEPARATOR . 'limit_' . md5($key) . '.json';
    }
}