<?php

declare(strict_types=1);

namespace Newla\Core\Container;

use Closure;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

class Container implements ContainerInterface
{
    private static ?Container $instance = null;
    protected array $bindings = [];
    protected array $instances = [];
    protected array $aliases = [];

    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public static function setInstance(?Container $container): void
    {
        static::$instance = $container;
    }

    public function bind(string $abstract, mixed $concrete = null, bool $shared = false): void
    {
        if ($concrete === null) {
            $concrete = $abstract;
        }

        if (is_object($concrete) && !$concrete instanceof Closure) {
            $this->instance($abstract, $concrete);
            return;
        }

        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'shared' => $shared,
        ];
    }

    public function singleton(string $abstract, mixed $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    public function instance(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }

    public function make(string $abstract, array $parameters = []): mixed
    {
        return $this->resolve($abstract, $parameters);
    }

    public function resolve(string $abstract, array $parameters = []): mixed
    {
        $abstract = $this->aliases[$abstract] ?? $abstract;

        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $concrete = $this->bindings[$abstract]['concrete'] ?? $abstract;

        if ($concrete === $abstract || $concrete instanceof Closure) {
            $object = $this->build($concrete, $parameters);
        } else {
            $object = $this->resolve($concrete, $parameters);
        }

        if (isset($this->bindings[$abstract]) && $this->bindings[$abstract]['shared']) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    public function build(mixed $concrete, array $parameters = []): mixed
    {
        if ($concrete instanceof Closure) {
            return $concrete($this, $parameters);
        }

        if (!class_exists($concrete)) {
            throw new BindingResolutionException("Target class [$concrete] does not exist.");
        }

        $reflector = new ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new BindingResolutionException("Target [$concrete] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            return new $concrete();
        }

        $dependencies = $this->resolveDependencies($constructor->getParameters(), $parameters);

        return $reflector->newInstanceArgs($dependencies);
    }

    public function call(callable|array|string $callable, array $parameters = []): mixed
    {
        if (is_array($callable)) {
            [$target, $method] = $callable;
            if (is_string($target)) {
                $target = $this->make($target);
            }
            $reflector = new ReflectionMethod($target, $method);
            $dependencies = $this->resolveDependencies($reflector->getParameters(), $parameters);
            return $reflector->invokeArgs($target, $dependencies);
        }

        if (is_string($callable) && str_contains($callable, '@')) {
            [$class, $method] = explode('@', $callable, 2);
            return $this->call([$this->make($class), $method], $parameters);
        }

        if ($callable instanceof Closure || is_string($callable)) {
            $reflector = new ReflectionFunction($callable);
            $dependencies = $this->resolveDependencies($reflector->getParameters(), $parameters);
            return $reflector->invokeArgs($dependencies);
        }

        return $callable(...$parameters);
    }

    /**
     * @param ReflectionParameter[] $parameters
     */
    protected function resolveDependencies(array $parameters, array $primitives = []): array
    {
        $dependencies = [];

        foreach ($parameters as $param) {
            $name = $param->getName();

            if (array_key_exists($name, $primitives)) {
                $dependencies[] = $primitives[$name];
                continue;
            }

            $type = $param->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $className = $type->getName();
                try {
                    $dependencies[] = $this->make($className);
                    continue;
                } catch (BindingResolutionException $e) {
                    if ($param->isDefaultValueAvailable()) {
                        $dependencies[] = $param->getDefaultValue();
                        continue;
                    }
                    if ($param->allowsNull()) {
                        $dependencies[] = null;
                        continue;
                    }
                    throw $e;
                }
            }

            if ($param->isDefaultValueAvailable()) {
                $dependencies[] = $param->getDefaultValue();
            } elseif ($param->allowsNull()) {
                $dependencies[] = null;
            } else {
                throw new BindingResolutionException("Unresolvable dependency parameter [\${$name}] in class/method.");
            }
        }

        return $dependencies;
    }

    public function alias(string $abstract, string $alias): void
    {
        $this->aliases[$alias] = $abstract;
    }
}