<?php

declare(strict_types=1);

namespace Newla\Core\Database;

use Closure;
use InvalidArgumentException;

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

    /**
     * Set the target table.
     * Note: Do NOT pass unsanitized user input directly to this method.
     */
    public function table(string $table): static
    {
        $this->validateIdentifier($table, 'table');
        $this->table = $table;
        return $this;
    }

    public function from(string $table): static
    {
        return $this->table($table);
    }

    /**
     * Set the selected columns.
     * Note: Do NOT pass unsanitized user input directly to this method.
     */
    public function select(string|array ...$columns): static
    {
        $cols = [];
        foreach ($columns as $col) {
            if (is_array($col)) {
                foreach ($col as $c) {
                    $this->validateIdentifier((string) $c, 'column');
                    $cols[] = (string) $c;
                }
            } else {
                $this->validateIdentifier($col, 'column');
                $cols[] = $col;
            }
        }
        $this->columns = !empty($cols) ? $cols : ['*'];
        return $this;
    }

    /**
     * Select a raw SQL expression.
     */
    public function selectRaw(string $sql, array $bindings = []): static
    {
        $this->columns = [$sql];
        $this->bindings = array_merge($this->bindings, $bindings);
        return $this;
    }

    public function where(string|Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'AND'): static
    {
        if ($column instanceof Closure) {
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

        $this->validateIdentifier((string) $column, 'column');

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
        $this->validateIdentifier($column, 'column');
        $this->wheres[] = [
            'type' => 'Null',
            'column' => $column,
            'boolean' => $boolean,
        ];
        return $this;
    }

    public function whereNotNull(string $column, string $boolean = 'AND'): static
    {
        $this->validateIdentifier($column, 'column');
        $this->wheres[] = [
            'type' => 'NotNull',
            'column' => $column,
            'boolean' => $boolean,
        ];
        return $this;
    }

    public function whereIn(string $column, array $values, string $boolean = 'AND'): static
    {
        $this->validateIdentifier($column, 'column');
        $this->wheres[] = [
            'type' => 'In',
            'column' => $column,
            'values' => array_values($values),
            'boolean' => $boolean,
        ];
        foreach ($values as $val) {
            $this->bindings[] = $val;
        }
        return $this;
    }

    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): static
    {
        $this->validateIdentifier($table, 'table');
        $this->validateIdentifier($first, 'column');
        $this->validateIdentifier($second, 'column');
        $this->joins[] = compact('table', 'first', 'operator', 'second', 'type');
        return $this;
    }

    public function leftJoin(string $table, string $first, string $operator, string $second): static
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    /**
     * Add an "order by" clause to the query.
     * Note: Do NOT pass unsanitized user input directly to this method.
     */
    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $this->validateIdentifier($column, 'column');

        $upperDir = strtoupper(trim($direction));
        if ($upperDir !== 'ASC' && $upperDir !== 'DESC') {
            throw new InvalidArgumentException("Invalid order direction [{$direction}]. Direction must be 'ASC' or 'DESC'.");
        }

        $this->orders[] = ['column' => $column, 'direction' => $upperDir];
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
            $orderParts = array_map(fn($o) => "{$o['column']} {$o['direction']}", $this->orders);
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
                if (empty($where['values'])) {
                    // Safe no-match condition instead of invalid SQL 'IN ()'
                    $clauses[] = "{$prefix}0 = 1";
                } else {
                    $placeholders = implode(', ', array_fill(0, count($where['values']), '?'));
                    $clauses[] = "{$prefix}{$where['column']} IN ({$placeholders})";
                }
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
        $previousLimit = $this->limitValue;
        $this->limit(1);
        $results = $this->get();
        $this->limitValue = $previousLimit;
        return $results[0] ?? null;
    }

    public function find(int|string $id, string $key = 'id'): ?array
    {
        return $this->where($key, $id)->first();
    }

    public function count(string $column = '*'): int
    {
        $backupColumns = $this->columns;
        $backupLimit = $this->limitValue;
        $backupOffset = $this->offsetValue;

        $this->columns = ["COUNT({$column}) as aggregate"];
        $this->limitValue = 1;
        $this->offsetValue = null;

        $results = $this->connection->select($this->toSql(), $this->bindings);
        $row = $results[0] ?? null;

        // Restore state completely
        $this->columns = $backupColumns;
        $this->limitValue = $backupLimit;
        $this->offsetValue = $backupOffset;

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

        $isMulti = isset($values[0]) && is_array($values[0]);
        $rows = $isMulti ? $values : [$values];

        $columns = array_keys($rows[0]);
        foreach ($columns as $col) {
            $this->validateIdentifier((string) $col, 'column');
        }

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
            $this->validateIdentifier((string) $col, 'column');
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

    protected function validateIdentifier(string $identifier, string $type): void
    {
        $trimmed = trim($identifier);
        if ($trimmed === '*' || $trimmed === '') {
            return;
        }

        // Support COUNT(*), MAX(col), etc. or table.column or column as alias or `column`
        if (!preg_match('/^[a-zA-Z0-9_\.\*\`\s,\(\)]+$/', $trimmed) || str_contains($trimmed, ';') || str_contains($trimmed, '--')) {
            throw new InvalidArgumentException("Invalid {$type} name [{$identifier}]: potentially unsafe SQL identifier detected.");
        }
    }
}
