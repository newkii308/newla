<?php

declare(strict_types=1);

namespace Newla\Core\Container;

interface ContainerInterface
{
    public function bind(string $abstract, mixed $concrete = null, bool $shared = false): void;
    public function singleton(string $abstract, mixed $concrete = null): void;
    public function instance(string $abstract, mixed $instance): void;
    public function make(string $abstract, array $parameters = []): mixed;
    public function call(callable|array|string $callable, array $parameters = []): mixed;
    public function has(string $abstract): bool;
}