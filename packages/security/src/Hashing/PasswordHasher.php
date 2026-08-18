<?php

declare(strict_types=1);

namespace Newla\Security\Hashing;

use RuntimeException;

class PasswordHasher implements HasherInterface
{
    protected string $algo;
    protected array $options;

    public function __construct(string $algo = PASSWORD_DEFAULT, array $options = [])
    {
        $this->algo = $algo;
        $this->options = $options;
    }

    public function make(string $value, array $options = []): string
    {
        $hash = password_hash($value, $this->algo, array_merge($this->options, $options));
        if ($hash === false) {
            throw new RuntimeException('Password hashing failed.');
        }
        return $hash;
    }

    public function check(string $value, string $hashedValue): bool
    {
        if (strlen($hashedValue) === 0) {
            return false;
        }
        return password_verify($value, $hashedValue);
    }

    public function needsRehash(string $hashedValue, array $options = []): bool
    {
        return password_needs_rehash($hashedValue, $this->algo, array_merge($this->options, $options));
    }
}