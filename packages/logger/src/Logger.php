<?php

declare(strict_types=1);

namespace Newla\Logger;

use Newla\Logger\Handler\FileHandler;
use Newla\Logger\Handler\StderrHandler;

class Logger
{
    protected static array $channels = [];
    protected static ?string $defaultChannel = null;

    public static function channel(string $name = 'app'): LoggerChannel
    {
        if (!isset(static::$channels[$name])) {
            static::$channels[$name] = static::createDefaultChannel($name);
        }
        return static::$channels[$name];
    }

    public static function registerChannel(string $name, LoggerChannel $channel): void
    {
        static::$channels[$name] = $channel;
    }

    protected static function createDefaultChannel(string $name): LoggerChannel
    {
        $logDir = function_exists('storage_path') ? storage_path('logs') : (getcwd() . '/storage/logs');
        $filePath = "{$logDir}/{$name}.log";

        $channel = new LoggerChannel($name);
        $channel->addHandler(new FileHandler($filePath));

        // If in error or critical, also output to error.log
        if ($name !== 'error') {
            $channel->addHandler(new FileHandler("{$logDir}/error.log", LogLevel::ERROR));
        }

        return $channel;
    }

    public static function debug(string $message, array $context = []): void
    {
        static::channel()->debug($message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        static::channel()->info($message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        static::channel()->warning($message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        static::channel()->error($message, $context);
    }

    public static function critical(string $message, array $context = []): void
    {
        static::channel()->critical($message, $context);
    }
}