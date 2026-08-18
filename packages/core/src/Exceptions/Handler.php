<?php

declare(strict_types=1);

namespace Newla\Core\Exceptions;

use Newla\Core\Container\Container;
use Newla\Core\Http\Request;
use Newla\Core\Http\Response;
use Throwable;

class Handler
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function report(Throwable $e): void
    {
        // Try logging if logger is bound
        if ($this->container->has('logger')) {
            try {
                $logger = $this->container->make('logger');
                if (method_exists($logger, 'error')) {
                    $logger->error($e->getMessage(), [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            } catch (Throwable) {
                // Fail silently on logging error
            }
        }
    }

    public function render(Request $request, Throwable $e): Response
    {
        $statusCode = $e instanceof HttpException ? $e->getStatusCode() : 500;
        $debug = (bool) env('APP_DEBUG', false);

        if ($request->expectsJson()) {
            $payload = [
                'success' => false,
                'error' => [
                    'message' => $e->getMessage() ?: 'Internal Server Error',
                    'code' => $this->getErrorCode($statusCode, $e),
                ]
            ];

            if ($debug) {
                $payload['debug'] = [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => explode("\n", $e->getTraceAsString()),
                ];
            }

            return Response::json($payload, $statusCode);
        }

        if ($debug) {
            $html = $this->renderDebugHtml($e, $statusCode);
        } else {
            $html = $this->renderProductionHtml($statusCode, $e->getMessage());
        }

        return Response::html($html, $statusCode);
    }

    protected function getErrorCode(int $statusCode, Throwable $e): string
    {
        return match ($statusCode) {
            400 => 'BAD_REQUEST',
            401 => 'UNAUTHORIZED',
            403 => 'FORBIDDEN',
            404 => 'NOT_FOUND',
            405 => 'METHOD_NOT_ALLOWED',
            422 => 'VALIDATION_FAILED',
            429 => 'TOO_MANY_REQUESTS',
            default => 'SERVER_ERROR',
        };
    }

    protected function renderDebugHtml(Throwable $e, int $status): string
    {
        $title = htmlspecialchars(get_class($e) . ': ' . $e->getMessage(), ENT_QUOTES, 'UTF-8');
        $file = htmlspecialchars($e->getFile() . ':' . $e->getLine(), ENT_QUOTES, 'UTF-8');
        $trace = htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Error ({$status})</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #0f172a; color: #f8fafc; padding: 2rem; }
        .container { max-width: 1000px; margin: 0 auto; }
        .card { background: #1e293b; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; border: 1px solid #334155; }
        h1 { color: #f87171; font-size: 1.5rem; margin-top: 0; }
        .badge { background: #ef4444; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: bold; font-size: 0.875rem; }
        .file { color: #94a3b8; font-family: monospace; font-size: 0.95rem; margin-bottom: 1rem; }
        pre { background: #090d16; padding: 1rem; border-radius: 6px; overflow-x: auto; color: #38bdf8; font-size: 0.85rem; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <span class="badge">HTTP {$status}</span>
            <h1>{$title}</h1>
            <div class="file">in {$file}</div>
            <pre>{$trace}</pre>
        </div>
    </div>
</body>
</html>
HTML;
    }

    protected function renderProductionHtml(int $status, string $message): string
    {
        $msg = $status === 404 ? 'Page Not Found' : 'Server Error';
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{$status} - {$msg}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f8fafc; color: #334155; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .box { text-align: center; }
        h1 { font-size: 4rem; color: #0284c7; margin: 0; }
        p { font-size: 1.25rem; color: #64748b; margin-top: 0.5rem; }
    </style>
</head>
<body>
    <div class="box">
        <h1>{$status}</h1>
        <p>{$msg}</p>
    </div>
</body>
</html>
HTML;
    }
}