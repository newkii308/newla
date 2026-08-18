<?php

declare(strict_types=1);

namespace Newla\Cli\Command;

use Newla\Cli\Output\ConsoleOutput;

interface CommandInterface
{
    public function getName(): string;
    public function getDescription(): string;
    public function execute(array $args, array $options, ConsoleOutput $output): int;
}