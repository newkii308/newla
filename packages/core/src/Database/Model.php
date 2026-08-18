<?php

declare(strict_types=1);

namespace Newla\Core\Database;

use JsonSerializable;
use Newla\Core\Container\Container;
use Newla\Core\Support\Str;

abstract class Model implements JsonSerializable
{
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = [];
    protected array $attributes = [];
    protected array $original = [];
    protected bool $exists = false;

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    public function getTable(): string
    {
        if ($this->table === '') {
            $class = (new \ReflectionClass($this))->getShortName();
            return Str::snake(Str::plural($class));
        }
        return $this->table;
    }

    public function getKeyName(): string
    {
        return $this->primaryKey;
    }

    public function getKey(): mixed
    {
        return $this->attributes[$this->getKeyName()] ?? null;
    }

    public static function query(): QueryBuilder
    {
        $instance = new static();
        return Container::getInstance()->make('db')->table($instance->getTable());
    }

    public static function all(): array
    {
        $instance = new static();
        $rows = static::query()->get();
        return array_map(fn($row) => static::newFromBuilder($row), $rows);
    }

    public static function find(int|string $id): ?static
    {
        $instance = new static();
        $row = static::query()->where($instance->getKeyName(), $id)->first();
        return $row ? static::newFromBuilder($row) : null;
    }

    public static function findOrFail(int|string $id): static
    {
        $model = static::find($id);
        if (!$model) {
            throw new \RuntimeException("Model not found with ID [{$id}]");
        }
        return $model;
    }

    public static function where(string $column, mixed $operator = null, mixed $value = null): QueryBuilder
    {
        return static::query()->where(...func_get_args());
    }

    public static function create(array $attributes = []): static
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    public static function newFromBuilder(array $attributes = []): static
    {
        $model = new static();
        $model->setRawAttributes($attributes, true);
        return $model;
    }

    public function setRawAttributes(array $attributes, bool $sync = false): void
    {
        $this->attributes = $attributes;
        if ($sync) {
            $this->original = $attributes;
            $this->exists = true;
        }
    }

    public function fill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            if (empty($this->fillable) || in_array($key, $this->fillable, true)) {
                $this->setAttribute($key, $value);
            }
        }
        return $this;
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function getAttribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    public function __get(string $key): mixed
    {
        return $this->getAttribute($key);
    }

    public function __set(string $key, mixed $value): void
    {
        $this->setAttribute($key, $value);
    }

    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    public function __unset(string $key): void
    {
        unset($this->attributes[$key]);
    }

    public function save(): bool
    {
        $query = static::query();

        if ($this->exists) {
            $dirty = array_diff_assoc($this->attributes, $this->original);
            if (empty($dirty)) {
                return true;
            }
            if (isset($this->attributes['updated_at'])) {
                $dirty['updated_at'] = date('Y-m-d H:i:s');
                $this->attributes['updated_at'] = $dirty['updated_at'];
            }
            $query->where($this->getKeyName(), $this->getKey())->update($dirty);
            $this->original = $this->attributes;
            return true;
        }

        if (array_key_exists('created_at', $this->attributes) || in_array('created_at', $this->fillable, true)) {
            $this->attributes['created_at'] = $this->attributes['created_at'] ?? date('Y-m-d H:i:s');
            $this->attributes['updated_at'] = $this->attributes['updated_at'] ?? date('Y-m-d H:i:s');
        }

        $id = $query->insertGetId($this->attributes);
        if ($id) {
            $this->attributes[$this->getKeyName()] = $id;
        }

        $this->exists = true;
        $this->original = $this->attributes;
        return true;
    }

    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        static::query()->where($this->getKeyName(), $this->getKey())->delete();
        $this->exists = false;
        return true;
    }

    public function toArray(): array
    {
        $data = $this->attributes;
        foreach ($this->hidden as $key) {
            unset($data[$key]);
        }
        return $data;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}