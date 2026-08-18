<?php

declare(strict_types=1);

namespace Newla\Cli\Command;

use Newla\Cli\Output\ConsoleOutput;

class SelfUpdateCommand extends Command
{
    protected string $name = 'self-update';
    protected string $description = 'Update NEWLA CLI to the latest version';

    public function execute(array $args, array $options, ConsoleOutput $output): int
    {
        $output->writeln($output->color("Checking for latest NEWLA CLI updates from GitHub...", "1;36"));

        $cliScript = dirname(__DIR__, 2) . '/bin/newla';
        $remoteUrl = 'https://raw.githubusercontent.com/newkii308/newla/main/cli/bin/newla';

        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: NEWLA-CLI-Updater\r\n"
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ];
        $ctx = stream_context_create($opts);
        $latestContent = @file_get_contents($remoteUrl, false, $ctx);

        if ($latestContent && strlen($latestContent) > 100) {
            file_put_contents($cliScript, $latestContent);
            $output->success("NEWLA CLI updated to the latest version successfully!");
            return 0;
        }

        $output->warning("Could not download update directly. If you installed via Composer, run: composer global update newla/newla");
        return 0;
    }
}