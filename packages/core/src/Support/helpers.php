<?php

declare(strict_types=1);

use Newla\Core\Application;
use Newla\Core\Container\Container;
use Newla\Core\Environment\Env;
use Newla\Core\Http\JsonResponse;
use Newla\Core\Http\RedirectResponse;
use Newla\Core\Http\Request;
use Newla\Core\Http\Response;

if (!function_exists('app')) {
    function app(?string $abstract = null, array $parameters = []): mixed
    {
        $container = Container::getInstance();
        if ($abstract === null) {
            return $container;
        }
        return $container->make($abstract, $parameters);
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return Env::get($key, $default);
    }
}

if (!function_exists('config')) {
    function config(?string $key = null, mixed $default = null): mixed
    {
        $config = app('config');
        if ($key === null) {
            return $config;
        }
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $config->set($k, $v);
            }
            return null;
        }
        return $config->get($key, $default);
    }
}

if (!function_exists('response')) {
    function response(mixed $content = '', int $status = 200, array $headers = []): Response
    {
        return new Response($content, $status, $headers);
    }
}

if (!function_exists('json')) {
    function json(mixed $data = [], int $status = 200, array $headers = []): JsonResponse
    {
        return Response::json($data, $status, $headers);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url, int $status = 302, array $headers = []): RedirectResponse
    {
        return Response::redirect($url, $status, $headers);
    }
}

if (!function_exists('view')) {
    function view(string $view, array $data = []): Response
    {
        $engine = app('view');
        return new Response($engine->render($view, $data));
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        /** @var Application $app */
        $app = app(Application::class);
        return $app->basePath($path);
    }
}

if (!function_exists('app_path')) {
    function app_path(string $path = ''): string
    {
        return base_path('app' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }
}

if (!function_exists('config_path')) {
    function config_path(string $path = ''): string
    {
        return base_path('config' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return base_path('storage' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }
}

if (!function_exists('public_path')) {
    function public_path(string $path = ''): string
    {
        return base_path('public' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }
}

if (!function_exists('e')) {
    /**
     * Escape HTML entities for XSS protection.
     */
    function e(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (class_exists(\Newla\Security\Security::class)) {
            return \Newla\Security\Security::csrfToken();
        }
        return $_SESSION['_csrf_token'] ?? '';
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        $token = csrf_token();
        return '<input type="hidden" name="_csrf_token" value="' . e($token) . '">';
    }
}