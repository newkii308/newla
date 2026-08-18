<?php

declare(strict_types=1);

namespace Newla\Security\Headers;

use Closure;
use Newla\Core\Http\Request;
use Newla\Core\Http\Response;
use Newla\Core\Middleware\MiddlewareInterface;

class SecurityHeadersMiddleware implements MiddlewareInterface
{
    protected array $headers;

    public function __construct(array $customHeaders = [])
    {
        $this->headers = array_merge([
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
        ], $customHeaders);
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        return $response->withHeaders($this->headers);
    }
}