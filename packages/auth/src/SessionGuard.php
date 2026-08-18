<?php

declare(strict_types=1);

namespace Newla\Auth;

use Newla\Security\Security;
use Newla\Security\Token\TokenGenerator;

class SessionGuard
{
    protected string $name;
    protected string $userModel;
    protected mixed $user = null;
    protected bool $loggedOut = false;
    protected static ?string $dummyHash = null;

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

    protected static function getDummyHash(): string
    {
        if (static::$dummyHash === null) {
            static::$dummyHash = Security::hashPassword('__dummy_password_timing_defense_newla__');
        }
        return static::$dummyHash;
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
            Security::verifyPassword($password, static::getDummyHash());
            return false;
        }

        $user = $modelClass::where($field, $email)->first();
        if (!$user) {
            // Constant-time defense against user enumeration
            Security::verifyPassword($password, static::getDummyHash());
            return false;
        }

        $userHash = is_array($user) ? ($user['password'] ?? '') : ($user->password ?? '');

        if (!Security::verifyPassword($password, $userHash)) {
            return false;
        }

        $this->login($user, $remember);
        return true;
    }

    public function login(mixed $user, bool $remember = false): void
    {
        $this->ensureSession();
        $id = is_array($user) ? ($user['id'] ?? null) : ($user->id ?? null);
        $_SESSION["_auth_{$this->name}"] = $id;
        $this->user = $user;
        $this->loggedOut = false;

        // Session fixation protection
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            @session_regenerate_id(true);
        }

        if ($remember && $id !== null) {
            $this->queueRememberCookie($user);
        }
    }

    protected function queueRememberCookie(mixed $user): void
    {
        $token = TokenGenerator::randomBase64(32);
        $tokenHash = hash('sha256', $token);

        if (is_object($user)) {
            if ($user instanceof AuthenticatableInterface || method_exists($user, 'setRememberToken')) {
                $user->setRememberToken($tokenHash);
                if (method_exists($user, 'save')) {
                    $user->save();
                }
            } elseif (property_exists($user, 'remember_token')) {
                $user->remember_token = $tokenHash;
                if (method_exists($user, 'save')) {
                    $user->save();
                }
            }
        }

        $id = is_array($user) ? ($user['id'] ?? null) : ($user->id ?? null);
        $cookieValue = base64_encode("{$id}|{$token}");
        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);

        if (!headers_sent()) {
            setcookie(
                "remember_{$this->name}",
                $cookieValue,
                [
                    'expires' => time() + (86400 * 30),
                    'path' => '/',
                    'domain' => '',
                    'secure' => $isSecure,
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]
            );
        }
    }

    public function logout(): void
    {
        $this->ensureSession();
        unset($_SESSION["_auth_{$this->name}"]);
        $this->user = null;
        $this->loggedOut = true;

        if (isset($_COOKIE["remember_{$this->name}"]) && !headers_sent()) {
            setcookie("remember_{$this->name}", '', time() - 3600, '/', '', false, true);
            unset($_COOKIE["remember_{$this->name}"]);
        }
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
        $modelClass = $this->userModel;

        if ($id !== null && class_exists($modelClass)) {
            $this->user = $modelClass::find($id);
            if ($this->user) {
                return $this->user;
            }
        }

        // Try remember-me cookie auto-login
        if ($this->user === null && isset($_COOKIE["remember_{$this->name}"]) && class_exists($modelClass)) {
            $this->user = $this->loginViaRememberCookie();
            if ($this->user) {
                return $this->user;
            }
        }

        return null;
    }

    protected function loginViaRememberCookie(): mixed
    {
        $raw = $_COOKIE["remember_{$this->name}"] ?? '';
        $decoded = base64_decode($raw, true);
        if (!$decoded || !str_contains($decoded, '|')) {
            return null;
        }

        [$id, $token] = explode('|', $decoded, 2);
        $modelClass = $this->userModel;
        $user = $modelClass::find($id);
        if (!$user) {
            return null;
        }

        $storedHash = '';
        if ($user instanceof AuthenticatableInterface || method_exists($user, 'getRememberToken')) {
            $storedHash = (string) $user->getRememberToken();
        } elseif (isset($user->remember_token)) {
            $storedHash = (string) $user->remember_token;
        }

        if (empty($storedHash) || !hash_equals($storedHash, hash('sha256', $token))) {
            return null;
        }

        // Rotate token and login
        $this->login($user, true);
        return $user;
    }

    public function id(): mixed
    {
        $this->ensureSession();
        return $_SESSION["_auth_{$this->name}"] ?? null;
    }
}
