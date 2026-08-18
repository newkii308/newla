<?php

declare(strict_types=1);

namespace Newla\Auth;

class Auth
{
    protected static ?SessionGuard $guard = null;

    public static function guard(string $name = 'web'): SessionGuard
    {
        if (static::$guard === null) {
            static::$guard = new SessionGuard($name);
        }
        return static::$guard;
    }

    public static function attempt(array $credentials = [], bool $remember = false): bool
    {
        return static::guard()->attempt($credentials, $remember);
    }

    public static function login(mixed $user): void
    {
        static::guard()->login($user);
    }

    public static function logout(): void
    {
        static::guard()->logout();
    }

    public static function check(): bool
    {
        return static::guard()->check();
    }

    public static function guest(): bool
    {
        return static::guard()->guest();
    }

    public static function user(): mixed
    {
        return static::guard()->user();
    }

    public static function id(): mixed
    {
        return static::guard()->id();
    }
}