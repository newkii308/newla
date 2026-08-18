<?php

declare(strict_types=1);

namespace Newla\Cli\Command;

use Newla\Cli\Output\ConsoleOutput;

class DbSeedCommand extends Command
{
    protected string $name = 'db:seed';
    protected string $description = 'Run database seeders';

    public function execute(array $args, array $options, ConsoleOutput $output): int
    {
        $cwd = $this->getProjectPath();
        $bootstrap = $cwd . '/bootstrap/app.php';
        if (file_exists($bootstrap)) {
            require_once $bootstrap;
        }

        $class = $options['class'] ?? ($args[0] ?? 'DatabaseSeeder');

        $filePath = $cwd . "/database/seeders/{$class}.php";
        if (!file_exists($filePath)) {
            $output->warning("Seeder file not found: database/seeders/{$class}.php");
            return 0;
        }

        require_once $filePath;
        $fqcn = "Database\\Seeders\\{$class}";
        if (class_exists($fqcn)) {
            $seeder = new $fqcn();
            $seeder->run();
            $output->success("Seeded: {$class}");
        } else {
            $output->error("Class [{$fqcn}] not found.");
            return 1;
        }

        return 0;
    }
}