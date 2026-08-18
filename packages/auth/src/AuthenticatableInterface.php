<?php

declare(strict_types=1);

namespace Newla\Auth;

interface AuthenticatableInterface
{
    public function getAuthIdentifier(): mixed;
    public function getAuthPassword(): string;
    public function getRememberToken(): ?string;
    public function setRememberToken(string $value): void;
}