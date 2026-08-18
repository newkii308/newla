<?php

declare(strict_types=1);

namespace Newla\Core\Database\Schema;

class Column
{
    public string $name;
    public string $type;
    public ?int $length = null;
    public bool $nullable = false;
    public mixed $default = null;
    public bool $hasDefault = false;
    public bool $autoIncrement = false;
    public bool $unique = false;
    public bool $primary = false;

    public function __construct(string $name, string $type, ?int $length = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->length = $length;
    }

    public function nullable(): static
    {
        $this->nullable = true;
        return $this;
    }

    public function default(mixed $value): static
    {
        $this->default = $value;
        $this->hasDefault = true;
        return $this;
    }

    public function unique(): static
    {
        $this->unique = true;
        return $this;
    }

    public function primary(): static
    {
        $this->primary = true;
        return $this;
    }

    public function autoIncrement(): static
    {
        $this->autoIncrement = true;
        return $this;
    }
}