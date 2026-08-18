<?php

declare(strict_types=1);

namespace Newla\Core\Http;

use Newla\Core\Support\Arr;

class Request
{
    protected array $query = [];
    protected array $request = [];
    protected array $attributes = [];
    protected array $cookies = [];
    protected array $files = [];
    protected array $server = [];
    protected array $headers = [];
    protected array $routeParams = [];
    protected ?string $rawBody = null;
    protected ?array $json = null;

    public function __construct(
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        ?string $content = null,
        array $headers = []
    ) {
        $this->query = $query;
        $this->request = $request;
        $this->attributes = $attributes;
        $this->cookies = $cookies;
        $this->files = $files;
        $this->server = $server;
        $this->rawBody = $content;
        $extracted = $this->extractHeaders($server);
        $this->headers = array_merge($extracted, array_change_key_case($headers, CASE_LOWER));
    }

    public static function capture(): static
    {
        $content = file_get_contents('php://input');
        return new static($_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER, $content ?: null);
    }

    public static function create(string $uri, string $method = 'GET', array $parameters = [], array $cookies = [], array $files = [], array $server = [], ?string $content = null): static
    {
        $server['REQUEST_URI'] = $uri;
        $server['REQUEST_METHOD'] = strtoupper($method);

        $parsed = parse_url($uri);
        $query = [];
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $query);
        }

        $post = [];
        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $post = $parameters;
        } else {
            $query = array_merge($query, $parameters);
        }

        return new static($query, $post, [], $cookies, $files, $server, $content);
    }

    protected function extractHeaders(array $server): array
    {
        $headers = [];
        foreach ($server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerKey = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$headerKey] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'], true)) {
                $headerKey = strtolower(str_replace('_', '-', $key));
                $headers[$headerKey] = $value;
            }
        }
        return $headers;
    }

    public function method(): string
    {
        $method = $this->server['REQUEST_METHOD'] ?? 'GET';
        if ($method === 'POST') {
            if (isset($this->request['_method'])) {
                return strtoupper((string) $this->request['_method']);
            }
            if ($this->hasHeader('x-http-method-override')) {
                return strtoupper((string) $this->header('x-http-method-override'));
            }
        }
        return strtoupper($method);
    }

    public function isMethod(string $method): bool
    {
        return $this->method() === strtoupper($method);
    }

    public function uri(): string
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }

    public function path(): string
    {
        $uri = $this->uri();
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        return '/' . trim($path, '/');
    }

    public function url(): string
    {
        $scheme = $this->isSecure() ? 'https' : 'http';
        $host = $this->header('host') ?? ($this->server['SERVER_NAME'] ?? 'localhost');
        return $scheme . '://' . $host . $this->path();
    }

    public function fullUrl(): string
    {
        $query = $this->server['QUERY_STRING'] ?? '';
        return $this->url() . ($query ? '?' . $query : '');
    }

    public function isSecure(): bool
    {
        return (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off')
            || ($this->server['SERVER_PORT'] ?? '') == 443
            || ($this->header('x-forwarded-proto') === 'https');
    }

    public const CLOUDFLARE_PROXIES = [
        '173.245.48.0/20',
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '141.101.64.0/18',
        '108.162.192.0/18',
        '190.93.240.0/20',
        '188.114.96.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '162.158.0.0/15',
        '104.16.0.0/13',
        '104.24.0.0/14',
        '172.64.0.0/13',
        '131.0.72.0/22',
    ];

    protected static array $trustedProxies = [];

    public static function setTrustedProxies(array $proxies): void
    {
        static::$trustedProxies = $proxies;
    }

    public static function getTrustedProxies(): array
    {
        return static::$trustedProxies;
    }

    public function isTrustedProxy(string $ip): bool
    {
        if (empty(static::$trustedProxies)) {
            return false;
        }

        foreach (static::$trustedProxies as $trusted) {
            if ($trusted === '*' || $trusted === $ip) {
                return true;
            }

            if (str_contains($trusted, '/')) {
                if ($this->ipMatchesCidr($ip, $trusted)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function ipMatchesCidr(string $ip, string $cidr): bool
    {
        [$subnet, $mask] = explode('/', $cidr, 2);
        $mask = (int) $mask;

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);
            $maskLong = -1 << (32 - $mask);
            return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
        }

        return false;
    }

    public function ip(): string
    {
        $remoteAddr = $this->server['REMOTE_ADDR'] ?? '127.0.0.1';

        if ($this->isTrustedProxy($remoteAddr)) {
            if ($cfIp = $this->header('cf-connecting-ip')) {
                $cfIp = trim($cfIp);
                if (filter_var($cfIp, FILTER_VALIDATE_IP)) {
                    return $cfIp;
                }
            }

            if ($forwarded = $this->header('x-forwarded-for')) {
                $ips = explode(',', $forwarded);
                $clientIp = trim($ips[0]);
                if (filter_var($clientIp, FILTER_VALIDATE_IP)) {
                    return $clientIp;
                }
            }

            if ($realIp = $this->header('x-real-ip')) {
                $realIp = trim($realIp);
                if (filter_var($realIp, FILTER_VALIDATE_IP)) {
                    return $realIp;
                }
            }
        }

        return $remoteAddr;
    }

    public function userAgent(): ?string
    {
        return $this->header('user-agent');
    }

    public function header(string $key, ?string $default = null): ?string
    {
        return $this->headers[strtolower($key)] ?? $default;
    }

    public function hasHeader(string $key): bool
    {
        return isset($this->headers[strtolower($key)]);
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function json(?string $key = null, mixed $default = null): mixed
    {
        if ($this->json === null) {
            $body = $this->getBody();
            $this->json = json_validate_data($body) ? json_decode($body, true) : [];
        }

        if ($key === null) {
            return $this->json;
        }

        return Arr::get($this->json, $key, $default);
    }

    public function getBody(): string
    {
        return $this->rawBody ?? '';
    }

    public function all(): array
    {
        $json = $this->isJson() ? ($this->json() ?: []) : [];
        return array_merge($this->query, $this->request, $json, $this->routeParams);
    }

    public function input(?string $key = null, mixed $default = null): mixed
    {
        $all = $this->all();
        if ($key === null) {
            return $all;
        }
        return Arr::get($all, $key, $default);
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->query;
        }
        return Arr::get($this->query, $key, $default);
    }

    public function post(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->request;
        }
        return Arr::get($this->request, $key, $default);
    }

    public function only(array|string $keys): array
    {
        return Arr::only($this->all(), (array) $keys);
    }

    public function except(array|string $keys): array
    {
        return Arr::except($this->all(), (array) $keys);
    }

    public function has(string|array $keys): bool
    {
        $all = $this->all();
        foreach ((array) $keys as $key) {
            if (!Arr::has($all, $key)) {
                return false;
            }
        }
        return true;
    }

    public function file(string $key): mixed
    {
        return $this->files[$key] ?? null;
    }

    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && is_array($this->files[$key]) && ($this->files[$key]['error'] ?? 1) === UPLOAD_ERR_OK;
    }

    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function route(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->routeParams;
        }
        return $this->routeParams[$key] ?? $default;
    }

    public function bearerToken(): ?string
    {
        $header = $this->header('authorization');
        if ($header && preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    public function isJson(): bool
    {
        $contentType = $this->header('content-type') ?? '';
        return str_contains($contentType, '/json') || str_contains($contentType, '+json');
    }

    public function expectsJson(): bool
    {
        $accept = $this->header('accept') ?? '';
        return $this->isJson() || str_contains($accept, '/json') || str_contains($accept, '+json');
    }

    public function ajax(): bool
    {
        return $this->header('x-requested-with') === 'XMLHttpRequest';
    }
}

if (!function_exists('json_validate_data')) {
    function json_validate_data(?string $string): bool
    {
        if ($string === null || trim($string) === '') {
            return false;
        }
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}