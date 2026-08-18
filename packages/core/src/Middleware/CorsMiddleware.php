<?php

declare(strict_types=1);

namespace Newla\Core\Middleware;

use Closure;
use Newla\Core\Http\Request;
use Newla\Core\Http\Response;

class CorsMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->header('origin') ?? '*';

        if ($request->method() === 'OPTIONS') {
            $response = new Response('', 204);
            return $this->attachCorsHeaders($response, $origin);
        }

        $response = $next($request);
        return $this->attachCorsHeaders($response, $origin);
    }

    protected function attachCorsHeaders(Response $response, string $origin): Response
    {
        return $response->withHeaders([
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age' => '86400',
        ]);
    }
}