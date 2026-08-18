<?php

declare(strict_types=1);

namespace Newla\Logger\Formatter;

class LineFormatter implements FormatterInterface
{
    protected string $dateFormat;

    public function __construct(string $dateFormat = 'Y-m-d H:i:s')
    {
        $this->dateFormat = $dateFormat;
    }

    public function format(string $channel, int $level, string $levelName, string $message, array $context = []): string
    {
        $date = date($this->dateFormat);
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';

        return "[{$date}] {$channel}.{$levelName}: {$message}{$contextStr}" . PHP_EOL;
    }
}