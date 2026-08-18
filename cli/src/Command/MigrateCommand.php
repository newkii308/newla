<?php

declare(strict_types=1);

namespace Newla\Cli\Command;

use Newla\Cli\Output\ConsoleOutput;
use Newla\Core\Database\Migrator;

class MigrateCommand extends Command
{
    protected string $name = 'migrate';
    protected string $description = 'Run pending database migrations';

    public function execute(array $args, array $options, ConsoleOutput $output): int
    {
        $cwd = $this->getProjectPath();
        $bootstrap = $cwd . '/bootstrap/app.php';

        if (file_exists($bootstrap)) {
            $app = require $bootstrap;
            $connection = $app->make('db')->connection();
        } else {
            $dbPath = $cwd . '/storage/database.sqlite';
            $dbDir = dirname($dbPath);
            if (!is_dir($dbDir)) mkdir($dbDir, 0777, true);
            $pdo = new \PDO("sqlite:{$dbPath}");
            $connection = new \Newla\Core\Database\Connection($pdo, 'sqlite');
        }

        $migrationsPath = $cwd . '/database/migrations';
        $migrator = new Migrator($connection, $migrationsPath);

        $output->writeln($output->color("Running database migrations...", "1;36"));
        $ran = $migrator->run();

        if (empty($ran)) {
            $output->info("Nothing to migrate. Database is up to date.");
        } else {
            foreach ($ran as $migration) {
                $output->success("Migrated: {$migration}");
            }
        }

        return 0;
    }
}