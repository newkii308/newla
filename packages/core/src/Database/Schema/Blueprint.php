<?php

declare(strict_types=1);

namespace Newla\Core\Database\Schema;

class Blueprint
{
    public string $table;
    /** @var Column[] */
    public array $columns = [];
    public array $commands = [];

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function id(string $name = 'id'): Column
    {
        $col = new Column($name, 'integer');
        $col->primary = true;
        $col->autoIncrement = true;
        $this->columns[] = $col;
        return $col;
    }

    public function increments(string $name): Column
    {
        return $this->id($name);
    }

    public function string(string $name, int $length = 255): Column
    {
        $col = new Column($name, 'string', $length);
        $this->columns[] = $col;
        return $col;
    }

    public function text(string $name): Column
    {
        $col = new Column($name, 'text');
        $this->columns[] = $col;
        return $col;
    }

    public function integer(string $name): Column
    {
        $col = new Column($name, 'integer');
        $this->columns[] = $col;
        return $col;
    }

    public function bigInteger(string $name): Column
    {
        $col = new Column($name, 'bigint');
        $this->columns[] = $col;
        return $col;
    }

    public function float(string $name): Column
    {
        $col = new Column($name, 'float');
        $this->columns[] = $col;
        return $col;
    }

    public function decimal(string $name, int $precision = 8, int $scale = 2): Column
    {
        $col = new Column($name, 'decimal');
        $this->columns[] = $col;
        return $col;
    }

    public function boolean(string $name): Column
    {
        $col = new Column($name, 'boolean');
        $this->columns[] = $col;
        return $col;
    }

    public function json(string $name): Column
    {
        $col = new Column($name, 'json');
        $this->columns[] = $col;
        return $col;
    }

    public function dateTime(string $name): Column
    {
        $col = new Column($name, 'datetime');
        $this->columns[] = $col;
        return $col;
    }

    public function timestamp(string $name): Column
    {
        $col = new Column($name, 'timestamp');
        $this->columns[] = $col;
        return $col;
    }

    public function timestamps(): void
    {
        $this->timestamp('created_at')->nullable();
        $this->timestamp('updated_at')->nullable();
    }

    public function toSql(string $driver): string
    {
        $colDefs = [];

        foreach ($this->columns as $col) {
            $def = "{$col->name} ";

            if ($col->primary && $col->autoIncrement) {
                $def .= match ($driver) {
                    'sqlite' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
                    'mysql' => 'BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY',
                    'pgsql' => 'SERIAL PRIMARY KEY',
                    default => 'INTEGER PRIMARY KEY',
                };
            } else {
                $def .= match ($col->type) {
                    'string' => "VARCHAR(" . ($col->length ?? 255) . ")",
                    'text' => "TEXT",
                    'integer' => "INTEGER",
                    'bigint' => match ($driver) { 'sqlite' => 'INTEGER', default => 'BIGINT' },
                    'float' => "FLOAT",
                    'decimal' => "DECIMAL(10,2)",
                    'boolean' => match ($driver) { 'sqlite' => 'INTEGER', default => 'BOOLEAN' },
                    'json' => match ($driver) { 'sqlite' => 'TEXT', default => 'JSON' },
                    'datetime', 'timestamp' => match ($driver) { 'sqlite' => 'DATETIME', default => 'TIMESTAMP' },
                    default => 'VARCHAR(255)',
                };

                if (!$col->nullable) {
                    $def .= ' NOT NULL';
                }

                if ($col->hasDefault) {
                    $def .= ' DEFAULT ' . (is_string($col->default) ? "'{$col->default}'" : (is_bool($col->default) ? ($col->default ? '1' : '0') : $col->default));
                }

                if ($col->unique) {
                    $def .= ' UNIQUE';
                }
            }

            $colDefs[] = $def;
        }

        return "CREATE TABLE IF NOT EXISTS {$this->table} (\n  " . implode(",\n  ", $colDefs) . "\n);";
    }
}