<?php

declare(strict_types=1);

namespace Newla\Cli\Command;

use Newla\Cli\Output\ConsoleOutput;

class MakeModelCommand extends Command
{
    protected string $name = 'make:model';
    protected string $description = 'Create a new Model class';

    public function execute(array $args, array $options, ConsoleOutput $output): int
    {
        $name = $args[0] ?? null;
        if (!$name) {
            $output->error("Please specify model name: newla make:model <Name>");
            return 1;
        }

        $cwd = $this->getProjectPath();
        $filePath = $cwd . "/app/Models/{$name}.php";

        if (file_exists($filePath)) {
            $output->error("Model [{$name}] already exists at app/Models/{$name}.php");
            return 1;
        }

        $tableName = strtolower(rtrim($name, 's')) . 's';

        $content = "<?php\n\ndeclare(strict_types=1);\n\nnamespace App\\Models;\n\nuse Newla\\Core\\Database\\Model;\n\nclass {$name} extends Model\n{\n    protected string \$table = '{$tableName}';\n    protected string \$primaryKey = 'id';\n    protected array \$fillable = [\n        // Add fillable columns here\n    ];\n}\n";

        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($filePath, $content);
        $output->success("Model created: app/Models/{$name}.php");
        return 0;
    }
}