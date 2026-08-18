<?php

declare(strict_types=1);

namespace Newla\Cli\Command;

use Newla\Cli\Output\ConsoleOutput;

class MakeMiddlewareCommand extends Command
{
    protected string $name = 'make:middleware';
    protected string $description = 'Create a new Middleware class';

    public function execute(array $args, array $options, ConsoleOutput $output): int
    {
        $name = $args[0] ?? null;
        if (!$name) {
            $output->error("Please specify middleware name: newla make:middleware <Name>");
            return 1;
        }

        if (!str_ends_with($name, 'Middleware')) {
            $name .= 'Middleware';
        }

        $cwd = $this->getProjectPath();
        $filePath = $cwd . "/app/Middleware/{$name}.php";

        if (file_exists($filePath)) {
            $output->error("Middleware [{$name}] already exists at app/Middleware/{$name}.php");
            return 1;
        }

        $content = "<?php\n\ndeclare(strict_types=1);\n\nnamespace App\\Middleware;\n\nuse Closure;\nuse Newla\\Core\\Http\\Request;\nuse Newla\\Core\\Http\\Response;\nuse Newla\\Core\\Middleware\\MiddlewareInterface;\n\nclass {$name} implements MiddlewareInterface\n{\n    public function handle(Request \$request, Closure \$next): Response\n    {\n        // Pre-middleware logic\n\n        \$response = \$next(\$request);\n\n        // Post-middleware logic\n\n        return \$response;\n    }\n}\n";

        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($filePath, $content);
        $output->success("Middleware created: app/Middleware/{$name}.php");
        return 0;
    }
}