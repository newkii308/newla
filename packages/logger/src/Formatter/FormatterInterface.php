<?php

declare(strict_types=1);

namespace Newla\Logger\Formatter;

interface FormatterInterface
{
    public function format(string $channel, int $level, string $levelName, string $message, array $context = []): string;
}