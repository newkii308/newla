<?php

declare(strict_types=1);

namespace Newla\Auth;

use Closure;
use Newla\Core\Exceptions\HttpException;
use Newla\Core\Http\Request;
use Newla\Core\Http\Response;
use Newla\Core\Middleware\MiddlewareInterface;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return Response::json([
                    'success' => false,
                    'error' => [
                        'message' => 'Unauthenticated.',
                        'code' => 'UNAUTHENTICATED',
                    ]
                ], 401);
            }

            return Response::redirect('/login');
        }

        return $next($request);
    }
}