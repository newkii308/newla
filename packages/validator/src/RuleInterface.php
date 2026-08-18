<?php

declare(strict_types=1);

namespace Newla\Validator;

interface RuleInterface
{
    public function passes(string $attribute, mixed $value, array $parameters = [], array $data = []): bool;
    public function message(string $attribute, array $parameters = []): string;
}