<?php

declare(strict_types=1);

namespace Newla\Logger\Handler;

use Newla\Logger\Formatter\FormatterInterface;
use Newla\Logger\Formatter\LineFormatter;

class FileHandler implements HandlerInterface
{
    protected string $filePath;
    protected int $minLevel;
    protected FormatterInterface $formatter;

    public function __construct(string $filePath, int $minLevel = 100, ?FormatterInterface $formatter = null)
    {
        $this->filePath = $filePath;
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

        $dir = dirname($this->filePath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $formatted = $this->formatter->format($channel, $level, $levelName, $message, $context);
        return (bool) @file_put_contents($this->filePath, $formatted, FILE_APPEND | LOCK_EX);
    }
}