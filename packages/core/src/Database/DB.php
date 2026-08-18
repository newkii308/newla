<?php

declare(strict_types=1);

namespace Newla\Core\Database;

use Closure;
use Newla\Core\Container\Container;

class DB
{
    protected static function getDb(): DatabaseManager
    {
        return Container::getInstance()->make('db');
    }

    public static function connection(?string $name = null): Connection
    {
        return static::getDb()->connection($name);
    }

    public static function table(string $table): QueryBuilder
    {
        return static::getDb()->table($table);
    }

    public static function select(string $query, array $bindings = []): array
    {
        return static::connection()->select($query, $bindings);
    }

    public static function selectOne(string $query, array $bindings = []): ?array
    {
        return static::connection()->selectOne($query, $bindings);
    }

    public static function insert(string $query, array $bindings = []): bool
    {
        return static::connection()->insert($query, $bindings);
    }

    public static function update(string $query, array $bindings = []): int
    {
        return static::connection()->update($query, $bindings);
    }

    public static function delete(string $query, array $bindings = []): int
    {
        return static::connection()->delete($query, $bindings);
    }

    public static function statement(string $query, array $bindings = []): bool
    {
        return static::connection()->statement($query, $bindings);
    }

    public static function transaction(Closure $callback): mixed
    {
        return static::connection()->transaction($callback);
    }
}