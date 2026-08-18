<?php

declare(strict_types=1);

namespace Newla\Cli\Command;

use Newla\Cli\Output\ConsoleOutput;

class MakeServiceCommand extends Command
{
    protected string $name = 'make:service';
    protected string $description = 'Create a new Service class';

    public function execute(array $args, array $options, ConsoleOutput $output): int
    {
        $name = $args[0] ?? null;
        if (!$name) {
            $output->error("Please specify service name: newla make:service <Name>");
            return 1;
        }

        if (!str_ends_with($name, 'Service')) {
            $name .= 'Service';
        }

        $cwd = $this->getProjectPath();
        $filePath = $cwd . "/app/Services/{$name}.php";

        if (file_exists($filePath)) {
            $output->error("Service [{$name}] already exists at app/Services/{$name}.php");
            return 1;
        }

        $content = "<?php\n\ndeclare(strict_types=1);\n\nnamespace App\\Services;\n\nclass {$name}\n{\n    public function execute(): mixed\n    {\n        // Business logic here\n        return true;\n    }\n}\n";

        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($filePath, $content);
        $output->success("Service created: app/Services/{$name}.php");
        return 0;
    }
}