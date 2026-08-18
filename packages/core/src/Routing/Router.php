<?php

declare(strict_types=1);

namespace Newla\Core\Routing;

use Closure;
use Newla\Core\Container\Container;
use Newla\Core\Exceptions\MethodNotAllowedException;
use Newla\Core\Exceptions\NotFoundException;
use Newla\Core\Http\Request;
use Newla\Core\Http\Response;
use Newla\Core\Middleware\MiddlewarePipeline;

class Router
{
    /** @var Route[] */
    protected array $routes = [];
    protected array $namedRoutes = [];
    protected array $groupStack = [];
    protected array $middlewareAliases = [];
    protected Container $container;

    public function __construct(?Container $container = null)
    {
        $this->container = $container ?? Container::getInstance();
    }

    public function get(string $uri, mixed $action): Route
    {
        return $this->addRoute(['GET', 'HEAD'], $uri, $action);
    }

    public function post(string $uri, mixed $action): Route
    {
        return $this->addRoute(['POST'], $uri, $action);
    }

    public function put(string $uri, mixed $action): Route
    {
        return $this->addRoute(['PUT'], $uri, $action);
    }

    public function patch(string $uri, mixed $action): Route
    {
        return $this->addRoute(['PATCH'], $uri, $action);
    }

    public function delete(string $uri, mixed $action): Route
    {
        return $this->addRoute(['DELETE'], $uri, $action);
    }

    public function options(string $uri, mixed $action): Route
    {
        return $this->addRoute(['OPTIONS'], $uri, $action);
    }

    public function any(string $uri, mixed $action): Route
    {
        return $this->addRoute(['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $uri, $action);
    }

    public function match(array $methods, string $uri, mixed $action): Route
    {
        return $this->addRoute($methods, $uri, $action);
    }

    public function middleware(string|array $middleware): RouteGroup
    {
        return (new RouteGroup($this))->middleware($middleware);
    }

    public function prefix(string $prefix): RouteGroup
    {
        return (new RouteGroup($this))->prefix($prefix);
    }

    public function group(array $attributes, Closure $callback): void
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
    }

    protected function addRoute(array $methods, string $uri, mixed $action): Route
    {
        $prefix = '';
        $middlewares = [];

        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
            if (isset($group['middleware'])) {
                $middlewares = array_merge($middlewares, (array) $group['middleware']);
            }
        }

        $fullUri = '/' . trim($prefix . '/' . trim($uri, '/'), '/');
        $route = new Route($methods, $fullUri, $action);
        if (!empty($middlewares)) {
            $route->middleware($middlewares);
        }

        $this->routes[] = $route;
        return $route;
    }

    public function aliasMiddleware(string $name, string $class): void
    {
        $this->middlewareAliases[$name] = $class;
    }

    public function aliasMiddlewares(array $aliases): void
    {
        $this->middlewareAliases = array_merge($this->middlewareAliases, $aliases);
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $path = $request->path();

        $matchedRoute = null;
        $methodNotAllowed = false;

        foreach ($this->routes as $route) {
            if ($route->matches($method, $path)) {
                $matchedRoute = $route;
                break;
            } elseif ($route->matchesUriOnly($path)) {
                $methodNotAllowed = true;
            }
        }

        if ($matchedRoute === null) {
            if ($methodNotAllowed) {
                throw new MethodNotAllowedException("Method [{$method}] not allowed for URI: {$path}");
            }
            throw new NotFoundException("Route not found for [{$method}] {$path}");
        }

        $request->setRouteParams($matchedRoute->getParameters());

        $resolvedMiddlewares = array_map(function ($mw) {
            return $this->middlewareAliases[$mw] ?? $mw;
        }, $matchedRoute->getMiddleware());

        $pipeline = new MiddlewarePipeline($this->container);
        return $pipeline->through($resolvedMiddlewares)->then($request, function (Request $req) use ($matchedRoute) {
            return $this->runAction($matchedRoute->getAction(), $req, $matchedRoute->getParameters());
        });
    }

    protected function runAction(mixed $action, Request $request, array $parameters): mixed
    {
        if ($action instanceof Closure) {
            return $this->container->call($action, array_merge(['request' => $request], $parameters));
        }

        if (is_array($action)) {
            [$controller, $method] = $action;
            return $this->container->call([$controller, $method], array_merge(['request' => $request], $parameters));
        }

        if (is_string($action) && str_contains($action, '@')) {
            [$controller, $method] = explode('@', $action, 2);
            return $this->container->call([$controller, $method], array_merge(['request' => $request], $parameters));
        }

        return $this->container->call($action, array_merge(['request' => $request], $parameters));
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}