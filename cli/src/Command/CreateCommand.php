<?php

declare(strict_types=1);

namespace Newla\Cli\Command;

use Newla\Cli\Output\ConsoleOutput;

class CreateCommand extends Command
{
    protected string $name = 'create';
    protected string $description = 'Create a new NEWLA project';

    public function execute(array $args, array $options, ConsoleOutput $output): int
    {
        $projectName = $args[0] ?? null;
        if (!$projectName) {
            $output->error("Please specify a project directory: newla create <project-name>");
            return 1;
        }

                if (str_starts_with($projectName, '/') || str_starts_with($projectName, '\\') || (isset($projectName[1]) && $projectName[1] === ':')) {
            $targetDir = $projectName;
        } else {
            $targetDir = rtrim(getcwd() ?: '.', '/\\') . DIRECTORY_SEPARATOR . $projectName;
        }
        if (is_dir($targetDir) && count(scandir($targetDir)) > 2) {
            $output->error("Directory [{$projectName}] already exists and is not empty.");
            return 1;
        }

        $output->writeln($output->color("Creating project [{$projectName}]...", "1;36"));
        $output->writeln();

        $steps = [
            'Directory structure' => fn() => $this->createDirectories($targetDir),
            'Configuration' => fn() => $this->createConfig($targetDir, $projectName),
            'Vendor & Core Packages' => fn() => $this->createVendor($targetDir),
            'Bootstrap' => fn() => $this->createBootstrap($targetDir),
            'Environment (.env)' => fn() => $this->createEnv($targetDir, $projectName),
            'Public Document Root' => fn() => $this->createPublic($targetDir),
            'Routes' => fn() => $this->createRoutes($targetDir),
            'Default Controller & View' => fn() => $this->createDefaults($targetDir),
            'Composer & Autoload' => fn() => $this->createComposer($targetDir, $projectName),
            'NEWLA manifest (newla.json)' => fn() => $this->createNewlaJson($targetDir, $projectName),
            'Gitignore & README' => fn() => $this->createGitFiles($targetDir, $projectName),
        ];

        foreach ($steps as $label => $step) {
            try {
                $step();
                $output->success($label);
            } catch (\Throwable $e) {
                $output->error("{$label}: " . $e->getMessage());
                return 1;
            }
        }

        $output->writeln();
        $output->writeln($output->color("Project created successfully!", "1;32"));
        $output->writeln();
        $output->writeln($output->color("Next steps:", "1;33"));
        $output->writeln("  cd {$projectName}");
        $output->writeln("  newla dev");
        $output->writeln();
        $output->writeln("Open http://127.0.0.1:8000 in your browser.");

        return 0;
    }

    protected function createDirectories(string $base): void
    {
        $dirs = [
            'app/Controllers',
            'app/Models',
            'app/Services',
            'app/Middleware',
            'app/Requests',
            'app/Providers',
            'bootstrap',
            'config',
            'database/migrations',
            'database/seeders',
            'public/assets/css',
            'public/assets/js',
            'public/assets/images',
            'resources/views/layouts',
            'resources/views/partials',
            'resources/assets',
            'routes',
            'storage/logs',
            'storage/cache',
            'storage/uploads',
            'tests/Unit',
            'tests/Feature',
        ];

        foreach ($dirs as $dir) {
            $path = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $dir);
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }
        }
    }

    protected function createVendor(string $base): void
    {
        $vendorDir = $base . DIRECTORY_SEPARATOR . 'vendor';
        if (!is_dir($vendorDir)) {
            mkdir($vendorDir, 0777, true);
        }

        $packagesSrc = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'packages';
        if (is_dir($packagesSrc)) {
            $this->copyDir($packagesSrc, $vendorDir . DIRECTORY_SEPARATOR . 'newla');
        }

        $autoloader = "<?php\n\ndeclare(strict_types=1);\n\n/**\n * NEWLA Standalone PSR-4 Autoloader\n * Generated automatically on project creation.\n */\n\nspl_autoload_register(function (string \$class) {\n    \$prefixes = [\n        'App\\\\' => dirname(__DIR__) . '/app/',\n        'Database\\\\' => dirname(__DIR__) . '/database/',\n        'Newla\\\\Core\\\\' => __DIR__ . '/newla/core/src/',\n        'Newla\\\\Security\\\\' => __DIR__ . '/newla/security/src/',\n        'Newla\\\\Validator\\\\' => __DIR__ . '/newla/validator/src/',\n        'Newla\\\\Logger\\\\' => __DIR__ . '/newla/logger/src/',\n        'Newla\\\\Storage\\\\' => __DIR__ . '/newla/storage/src/',\n        'Newla\\\\Image\\\\' => __DIR__ . '/newla/image/src/',\n        'Newla\\\\Auth\\\\' => __DIR__ . '/newla/auth/src/',\n        'Newla\\\\Api\\\\' => __DIR__ . '/newla/api/src/',\n    ];\n\n    foreach (\$prefixes as \$prefix => \$dir) {\n        if (str_starts_with(\$class, \$prefix)) {\n            \$relative = substr(\$class, strlen(\$prefix));\n            \$file = \$dir . str_replace('\\\\', DIRECTORY_SEPARATOR, \$relative) . '.php';\n            if (file_exists(\$file)) {\n                require_once \$file;\n                return;\n            }\n        }\n    }\n});\n\nif (file_exists(__DIR__ . '/newla/core/src/Support/helpers.php')) {\n    require_once __DIR__ . '/newla/core/src/Support/helpers.php';\n}\n";

        file_put_contents($vendorDir . '/autoload.php', $autoloader);
    }

    protected function copyDir(string $src, string $dst): void
    {
        $dir = @opendir($src);
        if (!$dir) return;

        if (!is_dir($dst)) {
            mkdir($dst, 0777, true);
        }

        while (false !== ($file = readdir($dir))) {
            if ($file !== '.' && $file !== '..') {
                $srcPath = $src . DIRECTORY_SEPARATOR . $file;
                $dstPath = $dst . DIRECTORY_SEPARATOR . $file;

                if (is_dir($srcPath)) {
                    $this->copyDir($srcPath, $dstPath);
                } else {
                    copy($srcPath, $dstPath);
                }
            }
        }
        closedir($dir);
    }

    protected function createConfig(string $base, string $name): void
    {
        $appConfig = "<?php\n\nreturn [\n    'name' => env('APP_NAME', 'NEWLA Application'),\n    'env' => env('APP_ENV', 'local'),\n    'debug' => (bool) env('APP_DEBUG', true),\n    'url' => env('APP_URL', 'http://127.0.0.1:8000'),\n    'timezone' => env('APP_TIMEZONE', 'UTC'),\n];\n";
        file_put_contents($base . '/config/app.php', $appConfig);

        $dbConfig = "<?php\n\nreturn [\n    'default' => env('DB_CONNECTION', 'sqlite'),\n    'connections' => [\n        'sqlite' => [\n            'driver' => 'sqlite',\n            'database' => env('DB_DATABASE', storage_path('database.sqlite')),\n        ],\n        'mysql' => [\n            'driver' => 'mysql',\n            'host' => env('DB_HOST', '127.0.0.1'),\n            'port' => (int) env('DB_PORT', 3306),\n            'database' => env('DB_DATABASE', 'newla'),\n            'username' => env('DB_USERNAME', 'root'),\n            'password' => env('DB_PASSWORD', ''),\n            'charset' => 'utf8mb4',\n        ],\n        'pgsql' => [\n            'driver' => 'pgsql',\n            'host' => env('DB_HOST', '127.0.0.1'),\n            'port' => (int) env('DB_PORT', 5432),\n            'database' => env('DB_DATABASE', 'newla'),\n            'username' => env('DB_USERNAME', 'postgres'),\n            'password' => env('DB_PASSWORD', ''),\n        ],\n    ],\n];\n";
        file_put_contents($base . '/config/database.php', $dbConfig);

        $storageConfig = "<?php\n\nreturn [\n    'default' => env('STORAGE_DRIVER', 'local'),\n    'disks' => [\n        'local' => [\n            'driver' => 'local',\n            'root' => storage_path('uploads'),\n            'url' => env('APP_URL', 'http://127.0.0.1:8000') . '/storage',\n        ],\n        'r2' => [\n            'driver' => 's3',\n            'key' => env('R2_ACCESS_KEY_ID'),\n            'secret' => env('R2_SECRET_ACCESS_KEY'),\n            'bucket' => env('R2_BUCKET'),\n            'endpoint' => env('R2_ENDPOINT'),\n            'url' => env('R2_PUBLIC_URL'),\n        ],\n    ],\n];\n";
        file_put_contents($base . '/config/storage.php', $storageConfig);

        $logConfig = "<?php\n\nreturn [\n    'default' => env('LOG_CHANNEL', 'file'),\n    'channels' => [\n        'file' => [\n            'driver' => 'file',\n            'path' => storage_path('logs/app.log'),\n        ],\n        'stderr' => [\n            'driver' => 'stderr',\n        ],\n    ],\n];\n";
        file_put_contents($base . '/config/logging.php', $logConfig);
    }

    protected function createBootstrap(string $base): void
    {
        $content = "<?php\n\ndeclare(strict_types=1);\n\nrequire_once dirname(__DIR__) . '/vendor/autoload.php';\n\n\$app = new Newla\\Core\\Application(dirname(__DIR__));\n\nreturn \$app;\n";
        file_put_contents($base . '/bootstrap/app.php', $content);
    }

    protected function createEnv(string $base, string $name): void
    {
        $env = "APP_NAME={$name}\nAPP_ENV=local\nAPP_DEBUG=true\nAPP_URL=http://127.0.0.1:8000\nAPP_TIMEZONE=UTC\n\nDB_CONNECTION=sqlite\nDB_DATABASE=storage/database.sqlite\n# DB_CONNECTION=mysql\n# DB_HOST=127.0.0.1\n# DB_PORT=3306\n# DB_DATABASE=newla_db\n# DB_USERNAME=root\n# DB_PASSWORD=\n\nSTORAGE_DRIVER=local\nLOG_CHANNEL=file\n";
        file_put_contents($base . '/.env', $env);
        file_put_contents($base . '/.env.example', $env);
    }

    protected function createPublic(string $base): void
    {
        $indexContent = "<?php\n\ndeclare(strict_types=1);\n\n/**\n * NEWLA — The Native PHP Framework\n * Entry point for web requests.\n */\n\ndefine('NEWLA_START', microtime(true));\n\n/** @var \\Newla\\Core\\Application \$app */\n\$app = require_once __DIR__ . '/../bootstrap/app.php';\n\n\$app->run();\n";
        file_put_contents($base . '/public/index.php', $indexContent);

        $htaccess = "<IfModule mod_rewrite.c>\n    <IfModule mod_negotiation.c>\n        Options -MultiViews -Indexes\n    </IfModule>\n\n    RewriteEngine On\n    RewriteCond %{HTTP:Authorization} .\n    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]\n\n    RewriteCond %{REQUEST_FILENAME} !-d\n    RewriteCond %{REQUEST_URI} (.+)/$\n    RewriteRule ^ %1 [L,R=301]\n\n    RewriteCond %{REQUEST_FILENAME} !-d\n    RewriteCond %{REQUEST_FILENAME} !-f\n    RewriteRule ^ index.php [L]\n</IfModule>\n";
        file_put_contents($base . '/public/.htaccess', $htaccess);
    }

    protected function createRoutes(string $base): void
    {
        $web = "<?php\n\ndeclare(strict_types=1);\n\nuse Newla\\Core\\Routing\\RouteFacade as Route;\nuse App\\Controllers\\HomeController;\n\nRoute::get('/', [HomeController::class, 'index']);\n";
        file_put_contents($base . '/routes/web.php', $web);

        $api = "<?php\n\ndeclare(strict_types=1);\n\nuse Newla\\Core\\Routing\\RouteFacade as Route;\n\nRoute::get('/ping', fn() => json(['pong' => true, 'timestamp' => time()]));\n";
        file_put_contents($base . '/routes/api.php', $api);
    }

    protected function createDefaults(string $base): void
    {
        $controller = "<?php\n\ndeclare(strict_types=1);\n\nnamespace App\\Controllers;\n\nuse Newla\\Core\\Http\\Request;\nuse Newla\\Core\\Http\\Response;\n\nclass HomeController\n{\n    public function index(Request \$request): Response\n    {\n        return view('home', [\n            'appName' => config('app.name', 'NEWLA Application'),\n            'phpVersion' => PHP_VERSION,\n            'time' => date('Y-m-d H:i:s'),\n        ]);\n    }\n}\n";
        file_put_contents($base . '/app/Controllers/HomeController.php', $controller);

        $layout = "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title><?= \$this->e(\$appName ?? 'NEWLA') ?></title>\n    <style>\n        * { box-sizing: border-box; margin: 0; padding: 0; }\n        body { font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif; background: #0b0f19; color: #e2e8f0; line-height: 1.6; min-height: 100vh; display: flex; flex-direction: column; }\n        .navbar { background: #111827; border-bottom: 1px solid #1f2937; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }\n        .logo { font-size: 1.25rem; font-weight: bold; color: #38bdf8; text-decoration: none; }\n        .container { max-width: 900px; margin: 3rem auto; padding: 0 1.5rem; flex: 1; }\n        .card { background: #1f2937; border-radius: 12px; padding: 2rem; border: 1px solid #374151; box-shadow: 0 10px 25px rgba(0,0,0,0.3); }\n        h1 { color: #38bdf8; font-size: 2.25rem; margin-bottom: 1rem; }\n        p { margin-bottom: 1rem; color: #94a3b8; font-size: 1.1rem; }\n        .badge { background: #0284c7; color: #fff; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 600; display: inline-block; margin-right: 0.5rem; }\n        .code { background: #0f172a; border-radius: 6px; padding: 1rem; color: #38bdf8; font-family: monospace; margin: 1.5rem 0; border: 1px solid #1e293b; }\n        .footer { text-align: center; padding: 2rem; color: #64748b; font-size: 0.875rem; border-top: 1px solid #1f2937; }\n    </style>\n</head>\n<body>\n    <header class=\"navbar\">\n        <a href=\"/\" class=\"logo\">⚡ NEWLA</a>\n        <div>\n            <span class=\"badge\">PHP <?= PHP_VERSION ?></span>\n            <span class=\"badge\" style=\"background:#10b981;\">Production Ready</span>\n        </div>\n    </header>\n\n    <main class=\"container\">\n        <?= \$this->yield('content') ?>\n    </main>\n\n    <footer class=\"footer\">\n        Powered by NEWLA — Native, Fast, Modular PHP Toolkit\n    </footer>\n</body>\n</html>\n";
        file_put_contents($base . '/resources/views/layouts/app.php', $layout);

        $view = "<?php \$this->layout('layouts/app'); ?>\n\n<div class=\"card\">\n    <h1>Welcome to <?= \$this->e(\$appName) ?>!</h1>\n    <p>Your new native PHP application is ready. Built with speed, simplicity, and security by default.</p>\n    \n    <div class=\"code\">\n        // Start building your routes in routes/web.php<br>\n        Route::get('/hello', function () {<br>\n        &nbsp;&nbsp;&nbsp;&nbsp;return json(['message' => 'Hello World!']);<br>\n        });\n    </div>\n\n    <p style=\"font-size: 0.95rem; color: #64748b;\">\n        Current Server Time: <?= \$this->e(\$time) ?>\n    </p>\n</div>\n";
        file_put_contents($base . '/resources/views/home.php', $view);
    }

    protected function createComposer(string $base, string $name): void
    {
        $composer = [
            'name' => "app/{$name}",
            'description' => "NEWLA Application: {$name}",
            'type' => 'project',
            'license' => 'proprietary',
            'require' => [
                'php' => '>=8.2',
                'newla/newla' => '^1.0'
            ],
            'autoload' => [
                'psr-4' => [
                    'App\\' => 'app/'
                ]
            ],
            'autoload-dev' => [
                'psr-4' => [
                    'Tests\\' => 'tests/'
                ]
            ]
        ];

        file_put_contents($base . '/composer.json', json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    protected function createNewlaJson(string $base, string $name): void
    {
        $manifest = [
            'name' => $name,
            'version' => '1.0.0',
            'framework' => 'newla',
            'packages' => [
                'core' => '^1.0'
            ]
        ];

        file_put_contents($base . '/newla.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    protected function createGitFiles(string $base, string $name): void
    {
        $gitignore = ".env\nstorage/logs/*.log\nstorage/cache/*\nstorage/database.sqlite\n";
        file_put_contents($base . '/.gitignore', $gitignore);

        $readme = "# {$name}\n\nBuilt with [NEWLA Framework](https://github.com/newkii308/newla).\n\n## Development\n\n```bash\nnewla dev\n```\n";
        file_put_contents($base . '/README.md', $readme);
    }
}