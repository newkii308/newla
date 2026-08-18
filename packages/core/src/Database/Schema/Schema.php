<?php

declare(strict_types=1);

namespace Newla\Core\Database\Schema;

use Closure;
use Newla\Core\Container\Container;
use Newla\Core\Database\Connection;

class Schema
{
    protected static function getConnection(): Connection
    {
        return Container::getInstance()->make('db')->connection();
    }

    public static function create(string $table, Closure $callback): void
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);

        $conn = static::getConnection();
        $sql = $blueprint->toSql($conn->getDriver());
        $conn->statement($sql);
    }

    public static function dropIfExists(string $table): void
    {
        $conn = static::getConnection();
        $conn->statement("DROP TABLE IF EXISTS {$table};");
    }

    public static function hasTable(string $table): bool
    {
        $conn = static::getConnection();
        $driver = $conn->getDriver();

        return match ($driver) {
            'sqlite' => (bool) $conn->selectOne("SELECT name FROM sqlite_master WHERE type='table' AND name = ?", [$table]),
            'mysql' => (bool) $conn->selectOne("SHOW TABLES LIKE ?", [$table]),
            'pgsql' => (bool) $conn->selectOne("SELECT tablename FROM pg_tables WHERE tablename = ?", [$table]),
            default => false,
        };
    }
}