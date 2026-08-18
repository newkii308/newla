<?php

declare(strict_types=1);

namespace Newla\Core\Routing;

use Closure;

class RouteGroup
{
    protected Router $router;
    protected array $attributes;

    public function __construct(Router $router, array $attributes = [])
    {
        $this->router = $router;
        $this->attributes = $attributes;
    }

    public function middleware(string|array $middleware): static
    {
        $current = (array) ($this->attributes['middleware'] ?? []);
        $this->attributes['middleware'] = array_merge($current, (array) $middleware);
        return $this;
    }

    public function prefix(string $prefix): static
    {
        $current = $this->attributes['prefix'] ?? '';
        $this->attributes['prefix'] = trim($current, '/') . '/' . trim($prefix, '/');
        return $this;
    }

    public function group(Closure $callback): void
    {
        $this->router->group($this->attributes, $callback);
    }
}