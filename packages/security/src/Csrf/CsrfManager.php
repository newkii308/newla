<?php

declare(strict_types=1);

namespace Newla\Security\Csrf;

use Newla\Security\Token\TokenGenerator;

class CsrfManager
{
    protected string $sessionKey;

    public function __construct(string $sessionKey = '_newla_csrf_token')
    {
        $this->sessionKey = $sessionKey;
        $this->ensureSession();
    }

    protected function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
    }

    public function getToken(): string
    {
        $this->ensureSession();
        if (empty($_SESSION[$this->sessionKey])) {
            $_SESSION[$this->sessionKey] = TokenGenerator::randomHex(32);
        }
        return $_SESSION[$this->sessionKey];
    }

    public function regenerateToken(): string
    {
        $this->ensureSession();
        $_SESSION[$this->sessionKey] = TokenGenerator::randomHex(32);
        return $_SESSION[$this->sessionKey];
    }

    public function validate(?string $token): bool
    {
        if ($token === null || $token === '') {
            return false;
        }

        $sessionToken = $this->getToken();
        return TokenGenerator::equals($sessionToken, $token);
    }
}