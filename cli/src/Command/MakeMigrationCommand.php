<?php

declare(strict_types=1);

namespace Newla\Cli\Command;

use Newla\Cli\Output\ConsoleOutput;

class MakeMigrationCommand extends Command
{
    protected string $name = 'make:migration';
    protected string $description = 'Create a new database migration file';

    public function execute(array $args, array $options, ConsoleOutput $output): int
    {
        $name = $args[0] ?? null;
        if (!$name) {
            $output->error("Please specify migration name: newla make:migration <name>");
            return 1;
        }

        $timestamp = date('Y_m_d_His');
        $fileName = "{$timestamp}_{$name}.php";

        $cwd = $this->getProjectPath();
        $filePath = $cwd . "/database/migrations/{$fileName}";

        $tableName = 'table_name';
        if (preg_match('/create_(.+)_table/', $name, $matches)) {
            $tableName = $matches[1];
        }

        $content = "<?php\n\ndeclare(strict_types=1);\n\nuse Newla\\Core\\Database\\Migration;\nuse Newla\\Core\\Database\\Schema\\Blueprint;\nuse Newla\\Core\\Database\\Schema\\Schema;\n\nreturn new class extends Migration {\n    public function up(): void\n    {\n        Schema::create('{$tableName}', function (Blueprint \$table) {\n            \$table->id();\n            // Define columns here\n            \$table->timestamps();\n        });\n    }\n\n    public function down(): void\n    {\n        Schema::dropIfExists('{$tableName}');\n    }\n};\n";

        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($filePath, $content);
        $output->success("Migration created: database/migrations/{$fileName}");
        return 0;
    }
}