<?php

declare(strict_types=1);

namespace Newla\Core\Http;

class Response
{
    protected mixed $content;
    protected int $statusCode;
    protected array $headers = [];

    public function __construct(mixed $content = '', int $status = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $status;
        $this->headers = $headers;
    }

    public static function json(mixed $data = [], int $status = 200, array $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }

    public static function html(string $html, int $status = 200, array $headers = []): static
    {
        $headers['Content-Type'] = 'text/html; charset=UTF-8';
        return new static($html, $status, $headers);
    }

    public static function redirect(string $url, int $status = 302, array $headers = []): RedirectResponse
    {
        return new RedirectResponse($url, $status, $headers);
    }

    public static function download(string $filePath, ?string $filename = null, array $headers = []): static
    {
        if (!file_exists($filePath)) {
            return new static('File not found', 404);
        }

        $filename = $filename ?? basename($filePath);
        $mime = mime_content_type($filePath) ?: 'application/octet-stream';

        $headers = array_merge([
            'Content-Type' => $mime,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => (string) filesize($filePath),
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ], $headers);

        $content = file_get_contents($filePath);
        return new static($content, 200, $headers);
    }

    public function setStatusCode(int $code): static
    {
        $this->statusCode = $code;
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function header(string $key, string $value): static
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function withHeaders(array $headers): static
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setContent(mixed $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getContent(): mixed
    {
        return $this->content;
    }

    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->statusCode);

            if (!isset($this->headers['Content-Type'])) {
                $this->headers['Content-Type'] = 'text/html; charset=UTF-8';
            }

            foreach ($this->headers as $name => $value) {
                header("{$name}: {$value}");
            }
        }

        echo (string) $this->content;
    }
}