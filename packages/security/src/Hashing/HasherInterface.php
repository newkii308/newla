<?php

declare(strict_types=1);

namespace Newla\Security\Hashing;

interface HasherInterface
{
    public function make(string $value, array $options = []): string;
    public function check(string $value, string $hashedValue): bool;
    public function needsRehash(string $hashedValue, array $options = []): bool;
}