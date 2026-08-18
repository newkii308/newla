<?php

declare(strict_types=1);

namespace Newla\Core\Database;

use Closure;
use PDO;
use PDOException;
use PDOStatement;

class Connection
{
    protected PDO $pdo;
    protected string $driver;
    protected array $config;

    public function __construct(PDO $pdo, string $driver, array $config = [])
    {
        $this->pdo = $pdo;
        $this->driver = $driver;
        $this->config = $config;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

    public function table(string $table): QueryBuilder
    {
        return (new QueryBuilder($this))->table($table);
    }

    public function select(string $query, array $bindings = []): array
    {
        $stmt = $this->executeStatement($query, $bindings);
        return $stmt->fetchAll();
    }

    public function selectOne(string $query, array $bindings = []): ?array
    {
        $records = $this->select($query, $bindings);
        return $records[0] ?? null;
    }

    public function insert(string $query, array $bindings = []): bool
    {
        return $this->statement($query, $bindings);
    }

    public function update(string $query, array $bindings = []): int
    {
        return $this->affectingStatement($query, $bindings);
    }

    public function delete(string $query, array $bindings = []): int
    {
        return $this->affectingStatement($query, $bindings);
    }

    public function statement(string $query, array $bindings = []): bool
    {
        $stmt = $this->executeStatement($query, $bindings);
        return $stmt !== false;
    }

    public function affectingStatement(string $query, array $bindings = []): int
    {
        $stmt = $this->executeStatement($query, $bindings);
        return $stmt->rowCount();
    }

    protected function executeStatement(string $query, array $bindings = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($bindings);
        return $stmt;
    }

    public function lastInsertId(?string $name = null): string|int
    {
        $id = $this->pdo->lastInsertId($name);
        return is_numeric($id) ? (int) $id : $id;
    }

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    public function transaction(Closure $callback): mixed
    {
        $this->beginTransaction();
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollBack();
            throw $e;
        }
    }
}