<?php

declare(strict_types=1);

namespace Newla\Logger;

use Newla\Logger\Handler\HandlerInterface;

class LoggerChannel
{
    protected string $name;
    /** @var HandlerInterface[] */
    protected array $handlers = [];

    public function __construct(string $name, array $handlers = [])
    {
        $this->name = $name;
        $this->handlers = $handlers;
    }

    public function addHandler(HandlerInterface $handler): static
    {
        $this->handlers[] = $handler;
        return $this;
    }

    public function log(int $level, string $message, array $context = []): void
    {
        $levelName = LogLevel::getName($level);
        foreach ($this->handlers as $handler) {
            $handler->handle($this->name, $level, $levelName, $message, $context);
        }
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function notice(string $message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function alert(string $message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function emergency(string $message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }
}