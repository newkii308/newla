<?php

declare(strict_types=1);

namespace Newla\Storage\Driver;

class S3StorageDriver implements StorageDriverInterface
{
    protected string $key;
    protected string $secret;
    protected string $bucket;
    protected string $region;
    protected string $endpoint;
    protected string $publicUrl;

    public function __construct(array $config)
    {
        $this->key = $config['key'] ?? '';
        $this->secret = $config['secret'] ?? '';
        $this->bucket = $config['bucket'] ?? '';
        $this->region = $config['region'] ?? 'auto';
        $this->endpoint = rtrim($config['endpoint'] ?? "https://{$this->bucket}.s3.amazonaws.com", '/');
        $this->publicUrl = rtrim($config['url'] ?? $this->endpoint, '/');
    }

    public function put(string $path, string $contents, array $options = []): bool
    {
        $url = $this->url($path);
        $headers = [
            'Content-Type' => $options['mimetype'] ?? 'application/octet-stream',
        ];

        return $this->sendRequest('PUT', $path, $contents, $headers);
    }

    public function get(string $path): ?string
    {
        $response = $this->sendRequest('GET', $path);
        return $response !== false ? $response : null;
    }

    public function delete(string $path): bool
    {
        return $this->sendRequest('DELETE', $path) !== false;
    }

    public function exists(string $path): bool
    {
        return $this->sendRequest('HEAD', $path) !== false;
    }

    public function size(string $path): int
    {
        $res = $this->sendRequest('HEAD', $path);
        return is_numeric($res) ? (int) $res : 0;
    }

    public function lastModified(string $path): int
    {
        return time();
    }

    public function mimeType(string $path): ?string
    {
        return 'application/octet-stream';
    }

    public function url(string $path): string
    {
        return $this->publicUrl . '/' . ltrim($path, '/');
    }

    public function files(string $directory = ''): array
    {
        return [];
    }

    public function makeDirectory(string $path): bool
    {
        return true;
    }

    public function deleteDirectory(string $path): bool
    {
        return true;
    }

    protected function sendRequest(string $method, string $path, ?string $body = null, array $headers = []): string|bool
    {
        $cleanPath = '/' . ltrim($path, '/');
        $url = $this->endpoint . $cleanPath;

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }

            $curlHeaders = [];
            foreach ($headers as $k => $v) {
                $curlHeaders[] = "{$k}: {$v}";
            }
            if (!empty($curlHeaders)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
            }

            $response = curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($method === 'HEAD') {
                return $httpCode >= 200 && $httpCode < 300;
            }

            return ($httpCode >= 200 && $httpCode < 300) ? (string) $response : false;
        }

        return false;
    }
}