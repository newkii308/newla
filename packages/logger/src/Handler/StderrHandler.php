<?php

declare(strict_types=1);

namespace Newla\Logger\Handler;

use Newla\Logger\Formatter\FormatterInterface;
use Newla\Logger\Formatter\LineFormatter;

class StderrHandler implements HandlerInterface
{
    protected int $minLevel;
    protected FormatterInterface $formatter;

    public function __construct(int $minLevel = 100, ?FormatterInterface $formatter = null)
    {
        $this->minLevel = $minLevel;
        $this->formatter = $formatter ?? new LineFormatter();
    }

    public function setFormatter(FormatterInterface $formatter): static
    {
        $this->formatter = $formatter;
        return $this;
    }

    public function getFormatter(): FormatterInterface
    {
        return $this->formatter;
    }

    public function handle(string $channel, int $level, string $levelName, string $message, array $context = []): bool
    {
        if ($level < $this->minLevel) {
            return false;
        }

        $formatted = $this->formatter->format($channel, $level, $levelName, $message, $context);
        fwrite(STDERR, $formatted);
        return true;
    }
}