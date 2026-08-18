<?php

declare(strict_types=1);

namespace Newla\Core\Routing;

use Closure;
use Newla\Core\Container\Container;

class RouteFacade
{
    protected static function getRouter(): Router
    {
        return Container::getInstance()->make('router');
    }

    public static function get(string $uri, mixed $action): Route
    {
        return static::getRouter()->get($uri, $action);
    }

    public static function post(string $uri, mixed $action): Route
    {
        return static::getRouter()->post($uri, $action);
    }

    public static function put(string $uri, mixed $action): Route
    {
        return static::getRouter()->put($uri, $action);
    }

    public static function patch(string $uri, mixed $action): Route
    {
        return static::getRouter()->patch($uri, $action);
    }

    public static function delete(string $uri, mixed $action): Route
    {
        return static::getRouter()->delete($uri, $action);
    }

    public static function any(string $uri, mixed $action): Route
    {
        return static::getRouter()->any($uri, $action);
    }

    public static function middleware(string|array $middleware): RouteGroup
    {
        return static::getRouter()->middleware($middleware);
    }

    public static function prefix(string $prefix): RouteGroup
    {
        return static::getRouter()->prefix($prefix);
    }

    public static function group(array $attributes, Closure $callback): void
    {
        static::getRouter()->group($attributes, $callback);
    }
}