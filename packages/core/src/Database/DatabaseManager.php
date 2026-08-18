<?php

declare(strict_types=1);

namespace Newla\Core\Database;

use InvalidArgumentException;
use PDO;

class DatabaseManager
{
    protected array $config;
    protected array $connections = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function connection(?string $name = null): Connection
    {
        $name = $name ?? $this->getDefaultConnection();

        if (!isset($this->connections[$name])) {
            $this->connections[$name] = $this->makeConnection($name);
        }

        return $this->connections[$name];
    }

    public function getDefaultConnection(): string
    {
        return $this->config['default'] ?? 'sqlite';
    }

    protected function makeConnection(string $name): Connection
    {
        $config = $this->config['connections'][$name] ?? null;
        if (!$config) {
            throw new InvalidArgumentException("Database connection [{$name}] not configured.");
        }

        $driver = $config['driver'] ?? 'sqlite';

        $pdo = match ($driver) {
            'sqlite' => $this->createSqlitePdo($config),
            'mysql' => $this->createMysqlPdo($config),
            'pgsql' => $this->createPgsqlPdo($config),
            default => throw new InvalidArgumentException("Unsupported database driver [{$driver}]."),
        };

        return new Connection($pdo, $driver, $config);
    }

    protected function createSqlitePdo(array $config): PDO
    {
        $database = $config['database'] ?? ':memory:';
        if ($database !== ':memory:') {
            if (!str_starts_with($database, '/') && !str_starts_with($database, '\\') && (!isset($database[1]) || $database[1] !== ':')) {
                $database = function_exists('base_path') ? base_path($database) : (getcwd() . DIRECTORY_SEPARATOR . $database);
            }
            $dir = dirname($database);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }

        return new PDO("sqlite:{$database}");
    }

    protected function createMysqlPdo(array $config): PDO
    {
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 3306;
        $database = $config['database'] ?? 'newla';
        $charset = $config['charset'] ?? 'utf8mb4';
        $username = $config['username'] ?? 'root';
        $password = $config['password'] ?? '';

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";
        return new PDO($dsn, $username, $password);
    }

    protected function createPgsqlPdo(array $config): PDO
    {
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 5432;
        $database = $config['database'] ?? 'newla';
        $username = $config['username'] ?? 'postgres';
        $password = $config['password'] ?? '';

        $dsn = "pgsql:host={$host};port={$port};dbname={$database}";
        return new PDO($dsn, $username, $password);
    }

    public function table(string $table): QueryBuilder
    {
        return $this->connection()->table($table);
    }
}