<?php

declare(strict_types=1);

namespace Newla\Core\Database;

use Closure;

class QueryBuilder
{
    protected Connection $connection;
    protected string $table = '';
    protected array $columns = ['*'];
    protected array $wheres = [];
    protected array $joins = [];
    protected array $orders = [];
    protected ?int $limitValue = null;
    protected ?int $offsetValue = null;
    protected array $bindings = [];

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function table(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    public function from(string $table): static
    {
        return $this->table($table);
    }

    public function select(string|array ...$columns): static
    {
        $cols = [];
        foreach ($columns as $col) {
            if (is_array($col)) {
                $cols = array_merge($cols, $col);
            } else {
                $cols[] = $col;
            }
        }
        $this->columns = !empty($cols) ? $cols : ['*'];
        return $this;
    }

    public function where(string|Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'AND'): static
    {
        if ($column instanceof Closure) {
            // Nested where
            $nested = new static($this->connection);
            $column($nested);
            $this->wheres[] = [
                'type' => 'Nested',
                'query' => $nested,
                'boolean' => $boolean,
            ];
            $this->bindings = array_merge($this->bindings, $nested->getBindings());
            return $this;
        }

        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'Basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => $boolean,
        ];

        $this->bindings[] = $value;
        return $this;
    }

    public function orWhere(string|Closure $column, mixed $operator = null, mixed $value = null): static
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        return $this->where($column, $operator, $value, 'OR');
    }

    public function whereNull(string $column, string $boolean = 'AND'): static
    {
        $this->wheres[] = [
            'type' => 'Null',
            'column' => $column,
            'boolean' => $boolean,
        ];
        return $this;
    }

    public function whereNotNull(string $column, string $boolean = 'AND'): static
    {
        $this->wheres[] = [
            'type' => 'NotNull',
            'column' => $column,
            'boolean' => $boolean,
        ];
        return $this;
    }

    public function whereIn(string $column, array $values, string $boolean = 'AND'): static
    {
        $this->wheres[] = [
            'type' => 'In',
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean,
        ];
        foreach ($values as $val) {
            $this->bindings[] = $val;
        }
        return $this;
    }

    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): static
    {
        $this->joins[] = compact('table', 'first', 'operator', 'second', 'type');
        return $this;
    }

    public function leftJoin(string $table, string $first, string $operator, string $second): static
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $this->orders[] = compact('column', 'direction');
        return $this;
    }

    public function limit(int $value): static
    {
        $this->limitValue = $value;
        return $this;
    }

    public function offset(int $value): static
    {
        $this->offsetValue = $value;
        return $this;
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }

    public function toSql(): string
    {
        $sql = 'SELECT ' . implode(', ', $this->columns) . ' FROM ' . $this->table;

        foreach ($this->joins as $join) {
            $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
        }

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }

        if (!empty($this->orders)) {
            $orderParts = array_map(fn($o) => "{$o['column']} " . strtoupper($o['direction']), $this->orders);
            $sql .= ' ORDER BY ' . implode(', ', $orderParts);
        }

        if ($this->limitValue !== null) {
            $sql .= ' LIMIT ' . $this->limitValue;
        }

        if ($this->offsetValue !== null) {
            $sql .= ' OFFSET ' . $this->offsetValue;
        }

        return $sql;
    }

    protected function compileWheres(): string
    {
        $clauses = [];

        foreach ($this->wheres as $i => $where) {
            $prefix = $i === 0 ? '' : $where['boolean'] . ' ';

            if ($where['type'] === 'Basic') {
                $clauses[] = "{$prefix}{$where['column']} {$where['operator']} ?";
            } elseif ($where['type'] === 'Null') {
                $clauses[] = "{$prefix}{$where['column']} IS NULL";
            } elseif ($where['type'] === 'NotNull') {
                $clauses[] = "{$prefix}{$where['column']} IS NOT NULL";
            } elseif ($where['type'] === 'In') {
                $placeholders = implode(', ', array_fill(0, count($where['values']), '?'));
                $clauses[] = "{$prefix}{$where['column']} IN ({$placeholders})";
            } elseif ($where['type'] === 'Nested') {
                $nestedSql = $where['query']->compileWheres();
                $clauses[] = "{$prefix}({$nestedSql})";
            }
        }

        return implode(' ', $clauses);
    }

    public function get(): array
    {
        return $this->connection->select($this->toSql(), $this->bindings);
    }

    public function first(): ?array
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    public function find(int|string $id, string $key = 'id'): ?array
    {
        return $this->where($key, $id)->first();
    }

    public function count(string $column = '*'): int
    {
        $this->columns = ["COUNT({$column}) as aggregate"];
        $row = $this->first();
        return (int) ($row['aggregate'] ?? 0);
    }

    public function exists(): bool
    {
        return $this->count() > 0;
    }

    public function pluck(string $column): array
    {
        $results = $this->select($column)->get();
        return array_column($results, $column);
    }

    public function insert(array $values): bool
    {
        if (empty($values)) {
            return true;
        }

        // Handle single row vs multiple rows
        $isMulti = isset($values[0]) && is_array($values[0]);
        $rows = $isMulti ? $values : [$values];

        $columns = array_keys($rows[0]);
        $columnList = implode(', ', $columns);
        $rowPlaceholders = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
        $allPlaceholders = implode(', ', array_fill(0, count($rows), $rowPlaceholders));

        $sql = "INSERT INTO {$this->table} ({$columnList}) VALUES {$allPlaceholders}";

        $bindings = [];
        foreach ($rows as $row) {
            foreach ($columns as $col) {
                $bindings[] = $row[$col] ?? null;
            }
        }

        return $this->connection->insert($sql, $bindings);
    }

    public function insertGetId(array $values, ?string $sequence = null): int|string
    {
        $this->insert($values);
        return $this->connection->lastInsertId($sequence);
    }

    public function update(array $values): int
    {
        if (empty($values)) {
            return 0;
        }

        $setParts = [];
        $bindings = [];

        foreach ($values as $col => $val) {
            $setParts[] = "{$col} = ?";
            $bindings[] = $val;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts);

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
            $bindings = array_merge($bindings, $this->bindings);
        }

        return $this->connection->update($sql, $bindings);
    }

    public function delete(): int
    {
        $sql = "DELETE FROM {$this->table}";

        $bindings = [];
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
            $bindings = $this->bindings;
        }

        return $this->connection->delete($sql, $bindings);
    }

    public function paginate(int $perPage = 15, int $page = 1): array
    {
        $page = max(1, $page);
        $total = $this->count();
        $lastPage = (int) ceil($total / $perPage);

        $this->columns = ['*'];
        $items = $this->offset(($page - 1) * $perPage)->limit($perPage)->get();

        return [
            'data' => $items,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $lastPage,
            'from' => $total > 0 ? ($page - 1) * $perPage + 1 : 0,
            'to' => min($page * $perPage, $total),
        ];
    }
}