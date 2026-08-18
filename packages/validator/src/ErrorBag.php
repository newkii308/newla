<?php

declare(strict_types=1);

namespace Newla\Validator;

use JsonSerializable;

class ErrorBag implements JsonSerializable
{
    protected array $messages = [];

    public function add(string $key, string $message): void
    {
        $this->messages[$key][] = $message;
    }

    public function has(string $key): bool
    {
        return !empty($this->messages[$key]);
    }

    public function first(?string $key = null, ?string $default = null): ?string
    {
        if ($key === null) {
            foreach ($this->messages as $fieldMessages) {
                if (!empty($fieldMessages)) {
                    return $fieldMessages[0];
                }
            }
            return $default;
        }

        return $this->messages[$key][0] ?? $default;
    }

    public function get(string $key): array
    {
        return $this->messages[$key] ?? [];
    }

    public function all(): array
    {
        return $this->messages;
    }

    public function any(): bool
    {
        return !empty($this->messages);
    }

    public function isEmpty(): bool
    {
        return empty($this->messages);
    }

    public function jsonSerialize(): array
    {
        return $this->all();
    }
}