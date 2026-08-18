<?php

declare(strict_types=1);

namespace Newla\Logger;

class LogLevel
{
    public const DEBUG = 100;
    public const INFO = 200;
    public const NOTICE = 250;
    public const WARNING = 300;
    public const ERROR = 400;
    public const CRITICAL = 500;
    public const ALERT = 550;
    public const EMERGENCY = 600;

    public static function getName(int $level): string
    {
        return match ($level) {
            self::DEBUG => 'DEBUG',
            self::INFO => 'INFO',
            self::NOTICE => 'NOTICE',
            self::WARNING => 'WARNING',
            self::ERROR => 'ERROR',
            self::CRITICAL => 'CRITICAL',
            self::ALERT => 'ALERT',
            self::EMERGENCY => 'EMERGENCY',
            default => 'LOG',
        };
    }

    public static function fromName(string $name): int
    {
        return match (strtoupper($name)) {
            'DEBUG' => self::DEBUG,
            'INFO' => self::INFO,
            'NOTICE' => self::NOTICE,
            'WARNING' => self::WARNING,
            'ERROR' => self::ERROR,
            'CRITICAL' => self::CRITICAL,
            'ALERT' => self::ALERT,
            'EMERGENCY' => self::EMERGENCY,
            default => self::INFO,
        };
    }
}