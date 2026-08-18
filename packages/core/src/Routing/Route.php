<?php

declare(strict_types=1);

namespace Newla\Core\Routing;

use Closure;

class Route
{
    protected array $methods;
    protected string $uri;
    protected mixed $action;
    protected array $middleware = [];
    protected ?string $name = null;
    protected array $wheres = [];
    protected array $parameters = [];

    public function __construct(array $methods, string $uri, mixed $action)
    {
        $this->methods = array_map('strtoupper', $methods);
        $this->uri = '/' . trim($uri, '/');
        $this->action = $action;
    }

    public function middleware(string|array $middleware): static
    {
        $this->middleware = array_merge($this->middleware, (array) $middleware);
        return $this;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function name(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function where(string|array $name, ?string $expression = null): static
    {
        if (is_array($name)) {
            $this->wheres = array_merge($this->wheres, $name);
        } else {
            $this->wheres[$name] = $expression;
        }
        return $this;
    }

    public function matches(string $method, string $path): bool
    {
        if (!in_array(strtoupper($method), $this->methods, true) && !in_array('ANY', $this->methods, true)) {
            return false;
        }

        $pattern = $this->compilePattern();
        if (preg_match($pattern, '/' . trim($path, '/'), $matches)) {
            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }
            $this->parameters = $params;
            return true;
        }

        return false;
    }

    public function matchesUriOnly(string $path): bool
    {
        $pattern = $this->compilePattern();
        return (bool) preg_match($pattern, '/' . trim($path, '/'));
    }

    protected function compilePattern(): string
    {
        $pattern = preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', function ($matches) {
            $param = $matches[1];
            $constraint = $this->wheres[$param] ?? '[^/]+';
            return '(?P<' . $param . '>' . $constraint . ')';
        }, $this->uri);

        return '#^' . $pattern . '$#';
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getAction(): mixed
    {
        return $this->action;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getUri(): string
    {
        return $this->uri;
    }
}