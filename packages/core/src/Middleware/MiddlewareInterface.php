<?php

declare(strict_types=1);

namespace Newla\Core\Middleware;

use Closure;
use Newla\Core\Http\Request;
use Newla\Core\Http\Response;

interface MiddlewareInterface
{
    public function handle(Request $request, Closure $next): Response;
}