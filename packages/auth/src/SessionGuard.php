<?php

declare(strict_types=1);

namespace Newla\Auth;

use Newla\Core\Container\Container;
use Newla\Security\Security;

class SessionGuard
{
    protected string $name;
    protected string $userModel;
    protected mixed $user = null;
    protected bool $loggedOut = false;

    public function __construct(string $name = 'web', string $userModel = '\\App\\Models\\User')
    {
        $this->name = $name;
        $this->userModel = $userModel;
        $this->ensureSession();
    }

    protected function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
    }

    public function attempt(array $credentials = [], bool $remember = false): bool
    {
        $email = $credentials['email'] ?? ($credentials['username'] ?? null);
        $password = $credentials['password'] ?? null;

        if (!$email || !$password) {
            return false;
        }

        $field = isset($credentials['email']) ? 'email' : 'username';
        $modelClass = $this->userModel;

        if (!class_exists($modelClass)) {
            return false;
        }

        $user = $modelClass::where($field, $email)->first();
        if (!$user) {
            return false;
        }

        $userHash = is_array($user) ? ($user['password'] ?? '') : ($user->password ?? '');

        if (!Security::verifyPassword($password, $userHash)) {
            return false;
        }

        $this->login($user);
        return true;
    }

    public function login(mixed $user): void
    {
        $this->ensureSession();
        $id = is_array($user) ? ($user['id'] ?? null) : ($user->id ?? null);
        $_SESSION["_auth_{$this->name}"] = $id;
        $this->user = $user;
        $this->loggedOut = false;

        // Session fixation protection
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_regenerate_id(true);
        }
    }

    public function logout(): void
    {
        $this->ensureSession();
        unset($_SESSION["_auth_{$this->name}"]);
        $this->user = null;
        $this->loggedOut = true;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    public function user(): mixed
    {
        if ($this->loggedOut) {
            return null;
        }

        if ($this->user !== null) {
            return $this->user;
        }

        $id = $this->id();
        if ($id === null) {
            return null;
        }

        $modelClass = $this->userModel;
        if (class_exists($modelClass)) {
            $this->user = $modelClass::find($id);
            return $this->user;
        }

        return null;
    }

    public function id(): mixed
    {
        $this->ensureSession();
        return $_SESSION["_auth_{$this->name}"] ?? null;
    }
}