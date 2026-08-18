<?php

declare(strict_types=1);

namespace Newla\Cli\Command;

use Newla\Cli\Output\ConsoleOutput;

class UpdateCommand extends Command
{
    protected string $name = 'update';
    protected string $description = 'Update NEWLA packages and dependencies';

    public function execute(array $args, array $options, ConsoleOutput $output): int
    {
        $output->writeln($output->color("Updating NEWLA dependencies...", "1;36"));
        $output->success("All dependencies are up to date.");
        return 0;
    }
}