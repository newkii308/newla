<?php

declare(strict_types=1);

namespace Newla\Security;

use Newla\Security\Csrf\CsrfManager;
use Newla\Security\Hashing\PasswordHasher;
use Newla\Security\RateLimit\RateLimiter;
use Newla\Security\Sanitizer\InputSanitizer;
use Newla\Security\Token\TokenGenerator;

class Security
{
    protected static ?CsrfManager $csrf = null;
    protected static ?PasswordHasher $hasher = null;
    protected static ?RateLimiter $limiter = null;

    public static function csrfToken(): string
    {
        if (static::$csrf === null) {
            static::$csrf = new CsrfManager();
        }
        return static::$csrf->getToken();
    }

    public static function verifyCsrf(?string $token): bool
    {
        if (static::$csrf === null) {
            static::$csrf = new CsrfManager();
        }
        return static::$csrf->validate($token);
    }

    public static function hashPassword(string $password, array $options = []): string
    {
        if (static::$hasher === null) {
            static::$hasher = new PasswordHasher();
        }
        return static::$hasher->make($password, $options);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        if (static::$hasher === null) {
            static::$hasher = new PasswordHasher();
        }
        return static::$hasher->check($password, $hash);
    }

    public static function randomToken(int $bytes = 32): string
    {
        return TokenGenerator::randomHex($bytes);
    }

    public static function rateLimiter(): RateLimiter
    {
        if (static::$limiter === null) {
            static::$limiter = new RateLimiter();
        }
        return static::$limiter;
    }

    public static function sanitize(mixed $value): mixed
    {
        return InputSanitizer::clean($value);
    }

    public static function escape(string $value): string
    {
        return InputSanitizer::escapeHtml($value);
    }
}