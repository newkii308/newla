<?php

declare(strict_types=1);

namespace Newla\Cli\Command;

use Newla\Cli\Output\ConsoleOutput;
use Newla\Core\Database\Migrator;

class MigrateRollbackCommand extends Command
{
    protected string $name = 'migrate:rollback';
    protected string $description = 'Rollback the last batch of database migrations';

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
        $output->writeln($output->color("Rolling back last migration batch...", "1;33"));
        $rolledBack = $migrator->rollback();

        if (empty($rolledBack)) {
            $output->info("Nothing to rollback.");
        } else {
            foreach ($rolledBack as $migration) {
                $output->success("Rolled back: {$migration}");
            }
        }

        return 0;
    }
}