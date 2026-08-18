<?php

declare(strict_types=1);

namespace Newla\Core;

use Newla\Core\Config\Repository as ConfigRepository;
use Newla\Core\Container\Container;
use Newla\Core\Database\DatabaseManager;
use Newla\Core\Environment\Env;
use Newla\Core\Exceptions\Handler;
use Newla\Core\Http\Request;
use Newla\Core\Http\Response;
use Newla\Core\Routing\Router;
use Newla\Core\View\ViewEngine;
use Throwable;

class Application extends Container
{
    const VERSION = '1.0.0';

    protected string $basePath;
    protected bool $booted = false;
    /** @var array */
    protected array $serviceProviders = [];

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/\\');
        static::setInstance($this);

        $this->instance('app', $this);
        $this->instance(Container::class, $this);
        $this->instance(Application::class, $this);

        $this->bootstrap();
    }

    public function basePath(string $path = ''): string
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : '');
    }

    protected function bootstrap(): void
    {
        // 0. Register App & Database PSR-4 autoloader
        spl_autoload_register(function (string $class) {
            if (str_starts_with($class, 'App\\')) {
                $relative = substr($class, 4);
                $file = $this->basePath('app/' . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php');
                if (file_exists($file)) {
                    require_once $file;
                }
            } elseif (str_starts_with($class, 'Database\\')) {
                $relative = substr($class, 9);
                $file = $this->basePath('database/' . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php');
                if (file_exists($file)) {
                    require_once $file;
                }
            }
        });

        // 1. Load Environment
        Env::load($this->basePath);

        // 2. Load Config
        $config = new ConfigRepository();
        $config->loadFromDirectory($this->basePath('config'));
        $this->instance('config', $config);

        // 3. Exception Handler
        $this->singleton('exception.handler', function () {
            return new Handler($this);
        });

        // 4. View Engine
        $this->singleton('view', function () {
            return new ViewEngine($this->basePath('resources/views'));
        });

        // 5. Router
        $this->singleton('router', function () {
            return new Router($this);
        });

        // 6. Database Manager
        $this->singleton('db', function () use ($config) {
            $dbConfig = $config->get('database', [
                'default' => env('DB_CONNECTION', 'sqlite'),
                'connections' => [
                    'sqlite' => [
                        'driver' => 'sqlite',
                        'database' => env('DB_DATABASE', $this->basePath('storage/database.sqlite')),
                    ],
                    'mysql' => [
                        'driver' => 'mysql',
                        'host' => env('DB_HOST', '127.0.0.1'),
                        'port' => (int) env('DB_PORT', 3306),
                        'database' => env('DB_DATABASE', 'newla'),
                        'username' => env('DB_USERNAME', 'root'),
                        'password' => env('DB_PASSWORD', ''),
                        'charset' => 'utf8mb4',
                    ]
                ]
            ]);
            return new DatabaseManager($dbConfig);
        });

        $this->alias('router', Router::class);
        $this->alias('db', DatabaseManager::class);
        $this->alias('config', ConfigRepository::class);
    }

    public function registerRoutes(): void
    {
        $webRoutes = $this->basePath('routes/web.php');
        if (file_exists($webRoutes)) {
            require $webRoutes;
        }

        $apiRoutes = $this->basePath('routes/api.php');
        if (file_exists($apiRoutes)) {
            $router = $this->make('router');
            $router->prefix('/api')->group(function () use ($apiRoutes) {
                require $apiRoutes;
            });
        }
    }

    public function handleRequest(Request $request): Response
    {
        $this->instance('request', $request);
        $this->instance(Request::class, $request);

        $this->registerRoutes();

        try {
            /** @var Router $router */
            $router = $this->make('router');
            return $router->dispatch($request);
        } catch (Throwable $e) {
            /** @var Handler $handler */
            $handler = $this->make('exception.handler');
            $handler->report($e);
            return $handler->render($request, $e);
        }
    }

    public function run(): void
    {
        $request = Request::capture();
        $response = $this->handleRequest($request);
        $response->send();
    }
}