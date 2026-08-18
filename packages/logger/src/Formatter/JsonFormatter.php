<?php

declare(strict_types=1);

namespace Newla\Logger\Formatter;

class JsonFormatter implements FormatterInterface
{
    public function format(string $channel, int $level, string $levelName, string $message, array $context = []): string
    {
        $data = [
            'timestamp' => date('c'),
            'channel' => $channel,
            'level' => $level,
            'level_name' => $levelName,
            'message' => $message,
            'context' => $context,
        ];

        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }
}