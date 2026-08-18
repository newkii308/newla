<?php

declare(strict_types=1);

namespace Newla\Cli\Command;

use Newla\Cli\Output\ConsoleOutput;

class InitCommand extends Command
{
    protected string $name = 'init';
    protected string $description = 'Initialize NEWLA in the current directory';

    public function execute(array $args, array $options, ConsoleOutput $output): int
    {
        $cwd = $this->getProjectPath();
        $name = basename($cwd);

        $create = new CreateCommand();
        return $create->execute(['.'], $options, $output);
    }
}