<?php

declare(strict_types=1);

namespace Newla\Core\Middleware;

use Closure;
use Newla\Core\Container\Container;
use Newla\Core\Http\Request;
use Newla\Core\Http\Response;

class MiddlewarePipeline
{
    protected array $pipes = [];
    protected Container $container;

    public function __construct(?Container $container = null)
    {
        $this->container = $container ?? Container::getInstance();
    }

    public function send(Request $request): static
    {
        $this->request = $request;
        return $this;
    }

    public function through(array $pipes): static
    {
        $this->pipes = $pipes;
        return $this;
    }

    public function then(Request $request, Closure $destination): Response
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes),
            $this->carry(),
            $this->prepareDestination($destination)
        );

        return $pipeline($request);
    }

    protected function carry(): Closure
    {
        return function (Closure $stack, mixed $pipe) {
            return function (Request $request) use ($stack, $pipe) {
                if ($pipe instanceof Closure) {
                    return $pipe($request, $stack);
                }

                if (is_string($pipe)) {
                    $pipeInstance = $this->container->make($pipe);
                    if ($pipeInstance instanceof MiddlewareInterface) {
                        return $pipeInstance->handle($request, $stack);
                    }
                    if (method_exists($pipeInstance, 'handle')) {
                        return $pipeInstance->handle($request, $stack);
                    }
                }

                if (is_object($pipe) && method_exists($pipe, 'handle')) {
                    return $pipe->handle($request, $stack);
                }

                return $stack($request);
            };
        };
    }

    protected function prepareDestination(Closure $destination): Closure
    {
        return function (Request $request) use ($destination) {
            $result = $destination($request);
            if (!$result instanceof Response) {
                if (is_array($result) || is_object($result)) {
                    return Response::json($result);
                }
                return new Response((string) $result);
            }
            return $result;
        };
    }
}