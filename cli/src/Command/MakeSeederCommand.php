<?php

declare(strict_types=1);

namespace Newla\Cli\Command;

use Newla\Cli\Output\ConsoleOutput;

class MakeSeederCommand extends Command
{
    protected string $name = 'make:seeder';
    protected string $description = 'Create a new database seeder class';

    public function execute(array $args, array $options, ConsoleOutput $output): int
    {
        $name = $args[0] ?? null;
        if (!$name) {
            $output->error("Please specify seeder name: newla make:seeder <Name>");
            return 1;
        }

        if (!str_ends_with($name, 'Seeder')) {
            $name .= 'Seeder';
        }

        $cwd = $this->getProjectPath();
        $filePath = $cwd . "/database/seeders/{$name}.php";

        if (file_exists($filePath)) {
            $output->error("Seeder [{$name}] already exists at database/seeders/{$name}.php");
            return 1;
        }

        $content = "<?php\n\ndeclare(strict_types=1);\n\nnamespace Database\\Seeders;\n\nuse Newla\\Core\\Database\\Seeder;\nuse Newla\\Core\\Database\\DB;\n\nclass {$name} extends Seeder\n{\n    public function run(): void\n    {\n        // Seed records here\n    }\n}\n";

        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($filePath, $content);
        $output->success("Seeder created: database/seeders/{$name}.php");
        return 0;
    }
}