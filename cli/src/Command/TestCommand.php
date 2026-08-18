<?php

declare(strict_types=1);

namespace Newla\Cli\Command;

use Newla\Cli\Output\ConsoleOutput;

class TestCommand extends Command
{
    protected string $name = 'test';
    protected string $description = 'Run the application test suite';

    public function execute(array $args, array $options, ConsoleOutput $output): int
    {
        $cwd = $this->getProjectPath();
        $phpunitBin = $cwd . '/vendor/bin/phpunit';
        if (DIRECTORY_SEPARATOR === '\\') {
            $phpunitBat = $cwd . '/vendor/bin/phpunit.bat';
            if (file_exists($phpunitBat)) {
                $phpunitBin = $phpunitBat;
            }
        }

        $filter = $options['filter'] ?? ($args[0] ?? null);
        $cmd = escapeshellcmd($phpunitBin);
        if ($filter) {
            $cmd .= ' --filter ' . escapeshellarg($filter);
        }

        if (file_exists($phpunitBin) || file_exists($cwd . '/vendor/bin/phpunit.bat')) {
            passthru($cmd, $code);
            return $code;
        }

        $output->info("Running NEWLA Built-in Test Suite...");
        $testRunner = $cwd . '/tests/runner.php';
        if (file_exists($testRunner)) {
            passthru(PHP_BINARY . ' ' . escapeshellarg($testRunner), $code);
            return $code;
        }

        $output->warning("No test runner found. Create tests in tests/ directory.");
        return 0;
    }
}