<?php

declare(strict_types=1);

namespace Newla\Security\RateLimit;

use Closure;
use Newla\Core\Exceptions\HttpException;
use Newla\Core\Http\Request;
use Newla\Core\Http\Response;
use Newla\Core\Middleware\MiddlewareInterface;

class RateLimitMiddleware implements MiddlewareInterface
{
    protected RateLimiter $limiter;
    protected int $maxAttempts;
    protected int $decayMinutes;

    public function __construct(?RateLimiter $limiter = null, int $maxAttempts = 60, int $decayMinutes = 1)
    {
        $this->limiter = $limiter ?? new RateLimiter();
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRequestSignature($request);
        $maxAttempts = $this->maxAttempts;

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = $this->limiter->availableIn($key);
            $headers = [
                'Retry-After' => (string) $retryAfter,
                'X-RateLimit-Limit' => (string) $maxAttempts,
                'X-RateLimit-Remaining' => '0',
            ];

            if ($request->expectsJson()) {
                return Response::json([
                    'success' => false,
                    'error' => [
                        'message' => 'Too Many Requests',
                        'code' => 'RATE_LIMIT_EXCEEDED',
                        'retry_after' => $retryAfter,
                    ]
                ], 429, $headers);
            }

            throw new HttpException(429, 'Too Many Requests', null, $headers);
        }

        $this->limiter->hit($key, $this->decayMinutes * 60);

        $response = $next($request);

        return $response->withHeaders([
            'X-RateLimit-Limit' => (string) $maxAttempts,
            'X-RateLimit-Remaining' => (string) $this->limiter->remaining($key, $maxAttempts),
        ]);
    }

    protected function resolveRequestSignature(Request $request): string
    {
        return sha1($request->ip() . '|' . $request->path());
    }
}