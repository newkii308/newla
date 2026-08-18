<?php

declare(strict_types=1);

namespace Newla\Security\Csrf;

use Closure;
use Newla\Core\Exceptions\HttpException;
use Newla\Core\Http\Request;
use Newla\Core\Http\Response;
use Newla\Core\Middleware\MiddlewareInterface;

class CsrfMiddleware implements MiddlewareInterface
{
    protected CsrfManager $manager;
    protected array $except = [];

    public function __construct(?CsrfManager $manager = null, array $except = [])
    {
        $this->manager = $manager ?? new CsrfManager();
        $this->except = $except;
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isReading($request) || $this->shouldSkip($request)) {
            return $next($request);
        }

        $token = $this->extractToken($request);

        if (!$this->manager->validate($token)) {
            if ($request->expectsJson()) {
                return Response::json([
                    'success' => false,
                    'error' => [
                        'message' => 'CSRF token mismatch.',
                        'code' => 'CSRF_MISMATCH'
                    ]
                ], 419);
            }
            throw new HttpException(419, 'CSRF token mismatch. Please reload the page.');
        }

        return $next($request);
    }

    protected function isReading(Request $request): bool
    {
        return in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true);
    }

    protected function shouldSkip(Request $request): bool
    {
        $path = $request->path();
        foreach ($this->except as $except) {
            if ($except === $path || (str_ends_with($except, '*') && str_starts_with($path, rtrim($except, '*')))) {
                return true;
            }
        }
        return false;
    }

    protected function extractToken(Request $request): ?string
    {
        return $request->input('_csrf_token')
            ?? $request->input('_token')
            ?? $request->header('x-csrf-token')
            ?? $request->header('x-xsrf-token');
    }
}