<?php

declare(strict_types=1);

namespace Newla\Cli\Command;

use Newla\Cli\Output\ConsoleOutput;

class DevCommand extends Command
{
    protected string $name = 'dev';
    protected string $description = 'Start the NEWLA local development server';

    public function execute(array $args, array $options, ConsoleOutput $output): int
    {
        $host = $options['host'] ?? '127.0.0.1';
        $port = $options['port'] ?? '8000';

        $cwd = $this->getProjectPath();
        $publicDir = $cwd . DIRECTORY_SEPARATOR . 'public';

        if (!is_dir($publicDir)) {
            $output->error("Public directory not found at [{$publicDir}]. Make sure you are in a NEWLA project root.");
            return 1;
        }

        $output->banner();
        $output->writeln($output->color("NEWLA Development Server", "1;32"));
        $output->writeln();
        $output->writeln("  Local:       " . $output->color("http://{$host}:{$port}", "1;36"));
        $output->writeln("  PHP:         " . PHP_VERSION);
        $output->writeln("  DocRoot:     " . $publicDir);
        $output->writeln("  Environment: " . (getenv('APP_ENV') ?: 'local'));
        $output->writeln();
        $output->writeln("Press " . $output->color("Ctrl+C", "1;33") . " to stop the server");
        $output->writeln();

        $cmd = sprintf(
            '%s -S %s:%s -t %s',
            PHP_BINARY,
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($publicDir)
        );

        passthru($cmd);
        return 0;
    }
}