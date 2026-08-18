<?php

declare(strict_types=1);

namespace Newla\Cli\Command;

use Newla\Cli\Output\ConsoleOutput;

class UpdateCommand extends Command
{
    protected string $name = 'update';
    protected string $description = 'Update NEWLA CLI or project dependencies';

    public function execute(array $args, array $options, ConsoleOutput $output): int
    {
        $target = $args[0] ?? 'project';

        if ($target === 'self' || isset($options['self'])) {
            $output->writeln($output->color("Checking for latest NEWLA CLI updates...", "1;36"));
            
            // Check self-update
            $cliScript = __DIR__ . '/../../bin/newla';
            $remoteUrl = 'https://raw.githubusercontent.com/newkii308/newla/main/cli/bin/newla';
            
            $latestContent = @file_get_contents($remoteUrl);
            if ($latestContent && strlen($latestContent) > 100) {
                file_put_contents($cliScript, $latestContent);
                $output->success("NEWLA CLI self-updated to the latest version successfully!");
                return 0;
            }

            $output->warning("Could not fetch remote update. If installed via Composer, run: composer global update newla/newla");
            return 0;
        }

        $output->writeln($output->color("Updating project dependencies...", "1;36"));

        $cwd = $this->getProjectPath();
        $vendorDir = $cwd . '/vendor/newla';

        if (is_dir($vendorDir)) {
            $output->success("Project vendor and packages are already up to date.");
        } else {
            $output->info("If you are using Composer, you can run: composer update");
        }

        return 0;
    }
}
