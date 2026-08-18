<?php

declare(strict_types=1);

namespace Newla\Cli\Command;

use Newla\Cli\Output\ConsoleOutput;

abstract class Command implements CommandInterface
{
    protected string $name = '';
    protected string $description = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    abstract public function execute(array $args, array $options, ConsoleOutput $output): int;

    protected function getProjectPath(): string
    {
        return getcwd() ?: '.';
    }

    protected function isInNewlaProject(): bool
    {
        $cwd = $this->getProjectPath();
        return file_exists($cwd . '/newla.json') || file_exists($cwd . '/bootstrap/app.php');
    }
}