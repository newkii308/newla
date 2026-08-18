<?php

declare(strict_types=1);

namespace Newla\Core\View;

use Newla\Core\Container\Container;
use Newla\Core\Http\Response;

class View
{
    public static function make(string $view, array $data = []): Response
    {
        $engine = Container::getInstance()->make('view');
        return new Response($engine->render($view, $data));
    }

    public static function share(string|array $key, mixed $value = null): void
    {
        Container::getInstance()->make('view')->share($key, $value);
    }
}