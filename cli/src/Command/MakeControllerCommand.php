<?php

declare(strict_types=1);

namespace Newla\Cli\Command;

use Newla\Cli\Output\ConsoleOutput;

class MakeControllerCommand extends Command
{
    protected string $name = 'make:controller';
    protected string $description = 'Create a new Controller class';

    public function execute(array $args, array $options, ConsoleOutput $output): int
    {
        $name = $args[0] ?? null;
        if (!$name) {
            $output->error("Please specify controller name: newla make:controller <Name>");
            return 1;
        }

        if (!str_ends_with($name, 'Controller')) {
            $name .= 'Controller';
        }

        $cwd = $this->getProjectPath();
        $filePath = $cwd . "/app/Controllers/{$name}.php";

        if (file_exists($filePath)) {
            $output->error("Controller [{$name}] already exists at app/Controllers/{$name}.php");
            return 1;
        }

        $content = "<?php\n\ndeclare(strict_types=1);\n\nnamespace App\\Controllers;\n\nuse Newla\\Core\\Http\\Request;\nuse Newla\\Core\\Http\\Response;\n\nclass {$name}\n{\n    public function index(Request \$request): Response\n    {\n        return json([\n            'message' => 'Hello from {$name}!',\n        ]);\n    }\n\n    public function show(Request \$request, int \$id): Response\n    {\n        return json([\n            'id' => \$id,\n        ]);\n    }\n\n    public function store(Request \$request): Response\n    {\n        return json([\n            'status' => 'created',\n            'data' => \$request->all(),\n        ], 201);\n    }\n}\n";

        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($filePath, $content);
        $output->success("Controller created: app/Controllers/{$name}.php");
        return 0;
    }
}