<?php

declare(strict_types=1);

namespace Newla\Cli\Command;

use Newla\Cli\Output\ConsoleOutput;
use Newla\Core\Database\Migrator;

class MigrateFreshCommand extends Command
{
    protected string $name = 'migrate:fresh';
    protected string $description = 'Drop all database tables and re-run all migrations';

    public function execute(array $args, array $options, ConsoleOutput $output): int
    {
        $cwd = $this->getProjectPath();
        $bootstrap = $cwd . '/bootstrap/app.php';

        if (file_exists($bootstrap)) {
            $app = require $bootstrap;
            $connection = $app->make('db')->connection();
        } else {
            $dbPath = $cwd . '/storage/database.sqlite';
            $pdo = new \PDO("sqlite:{$dbPath}");
            $connection = new \Newla\Core\Database\Connection($pdo, 'sqlite');
        }

        $migrator = new Migrator($connection, $cwd . '/database/migrations');
        $output->writeln($output->color("Dropping all tables and re-running migrations...", "1;33"));
        $migrator->fresh();
        $output->success("Database freshly migrated.");

        return 0;
    }
}