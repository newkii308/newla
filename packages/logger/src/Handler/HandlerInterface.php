<?php

declare(strict_types=1);

namespace Newla\Logger\Handler;

use Newla\Logger\Formatter\FormatterInterface;

interface HandlerInterface
{
    public function handle(string $channel, int $level, string $levelName, string $message, array $context = []): bool;
    public function setFormatter(FormatterInterface $formatter): static;
    public function getFormatter(): FormatterInterface;
}