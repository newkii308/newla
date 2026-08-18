<?php

declare(strict_types=1);

namespace Newla\Core\Database;

use Newla\Core\Database\Schema\Blueprint;
use Newla\Core\Database\Schema\Schema;

class Migrator
{
    protected Connection $connection;
    protected string $migrationsPath;

    public function __construct(Connection $connection, string $migrationsPath)
    {
        $this->connection = $connection;
        $this->migrationsPath = rtrim($migrationsPath, '/\\');
    }

    public function ensureTableExists(): void
    {
        if (!Schema::hasTable('migrations')) {
            Schema::create('migrations', function (Blueprint $table) {
                $table->id();
                $table->string('migration');
                $table->integer('batch');
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function run(): array
    {
        $this->ensureTableExists();

        $ran = $this->getRanMigrations();
        $files = $this->getMigrationFiles();
        $pending = array_diff(array_keys($files), $ran);

        if (empty($pending)) {
            return [];
        }

        $batch = $this->getNextBatchNumber();
        $executed = [];

        foreach ($pending as $name) {
            $file = $files[$name];
            $migration = require $file;

            if (is_object($migration) && $migration instanceof Migration) {
                $migration->up();
            } else {
                $class = $this->resolveClassName($name);
                if (class_exists($class)) {
                    $instance = new $class();
                    $instance->up();
                }
            }

            $this->connection->table('migrations')->insert([
                'migration' => $name,
                'batch' => $batch,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $executed[] = $name;
        }

        return $executed;
    }

    public function rollback(): array
    {
        $this->ensureTableExists();

        $lastBatch = $this->getLastBatchNumber();
        if ($lastBatch === 0) {
            return [];
        }

        $records = $this->connection->table('migrations')
            ->where('batch', $lastBatch)
            ->orderBy('id', 'DESC')
            ->get();

        $files = $this->getMigrationFiles();
        $rolledBack = [];

        foreach ($records as $record) {
            $name = $record['migration'];
            if (isset($files[$name])) {
                $file = $files[$name];
                $migration = require $file;

                if (is_object($migration) && $migration instanceof Migration) {
                    $migration->down();
                } else {
                    $class = $this->resolveClassName($name);
                    if (class_exists($class)) {
                        $instance = new $class();
                        $instance->down();
                    }
                }
            }

            $this->connection->table('migrations')->where('id', $record['id'])->delete();
            $rolledBack[] = $name;
        }

        return $rolledBack;
    }

    public function fresh(): void
    {
        // For fresh, drop all tables or rollback everything
        $driver = $this->connection->getDriver();
        if ($driver === 'sqlite') {
            $tables = $this->connection->select("SELECT name FROM sqlite_master WHERE type='table' AND name != 'sqlite_sequence'");
            foreach ($tables as $t) {
                Schema::dropIfExists($t['name']);
            }
        } elseif ($driver === 'mysql') {
            $this->connection->statement('SET FOREIGN_KEY_CHECKS = 0;');
            $tables = $this->connection->select('SHOW TABLES');
            foreach ($tables as $t) {
                $table = reset($t);
                Schema::dropIfExists($table);
            }
            $this->connection->statement('SET FOREIGN_KEY_CHECKS = 1;');
        }

        $this->run();
    }

    public function getRanMigrations(): array
    {
        return $this->connection->table('migrations')->pluck('migration');
    }

    public function getMigrationFiles(): array
    {
        if (!is_dir($this->migrationsPath)) {
            return [];
        }

        $files = glob($this->migrationsPath . '/*.php');
        $result = [];
        foreach ($files as $file) {
            $name = basename($file, '.php');
            $result[$name] = $file;
        }
        ksort($result);
        return $result;
    }

    protected function getNextBatchNumber(): int
    {
        return $this->getLastBatchNumber() + 1;
    }

    protected function getLastBatchNumber(): int
    {
        $max = $this->connection->table('migrations')->max ?? null;
        $row = $this->connection->selectOne('SELECT MAX(batch) as max_batch FROM migrations');
        return (int) ($row['max_batch'] ?? 0);
    }

    protected function resolveClassName(string $filename): string
    {
        $parts = explode('_', $filename);
        // remove date/timestamp prefix
        while (!empty($parts) && is_numeric($parts[0])) {
            array_shift($parts);
        }
        $name = implode('_', $parts);
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
    }
}