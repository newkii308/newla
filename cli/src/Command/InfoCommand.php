<?php

declare(strict_types=1);

namespace Newla\Cli\Command;

use Newla\Cli\Application;
use Newla\Cli\Output\ConsoleOutput;

class InfoCommand extends Command
{
    protected string $name = 'info';
    protected string $description = 'Display information about the current NEWLA environment';

    public function execute(array $args, array $options, ConsoleOutput $output): int
    {
        $output->banner();
        $output->writeln($output->color("Environment Information:", "1;33"));
        $output->writeln("  NEWLA CLI:       " . Application::VERSION);
        $output->writeln("  PHP Version:     " . PHP_VERSION);
        $output->writeln("  PHP SAPI:        " . PHP_SAPI);
        $output->writeln("  OS Family:       " . PHP_OS_FAMILY);
        $output->writeln("  OS:              " . PHP_OS);
        $output->writeln("  Architecture:    " . (PHP_INT_SIZE === 8 ? '64-bit' : '32-bit'));
        $output->writeln("  Working Dir:     " . getcwd());

        $cwd = $this->getProjectPath();
        if (file_exists($cwd . '/newla.json')) {
            $manifest = json_decode(file_get_contents($cwd . '/newla.json'), true);
            $output->writeln();
            $output->writeln($output->color("Project Information:", "1;33"));
            $output->writeln("  Project Name:    " . ($manifest['name'] ?? 'unknown'));
            $output->writeln("  Version:         " . ($manifest['version'] ?? '1.0.0'));
            $output->writeln("  Framework:       " . ($manifest['framework'] ?? 'newla'));
            
            $packages = $manifest['packages'] ?? [];
            $output->writeln("  Installed Pkgs:  " . (empty($packages) ? 'none' : implode(', ', array_keys($packages))));
        }

        return 0;
    }
}