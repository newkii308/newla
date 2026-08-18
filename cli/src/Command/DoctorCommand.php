<?php

declare(strict_types=1);

namespace Newla\Cli\Command;

use Newla\Cli\Output\ConsoleOutput;
use PDO;

class DoctorCommand extends Command
{
    protected string $name = 'doctor';
    protected string $description = 'Diagnose system requirements, permissions, and project health';

    public function execute(array $args, array $options, ConsoleOutput $output): int
    {
        $output->banner();
        $output->writeln($output->color("Running NEWLA Doctor...", "1;36"));
        $output->writeln();

        $checks = 0;
        $warnings = 0;
        $errors = 0;

        // 1. PHP Version
        $checks++;
        if (version_compare(PHP_VERSION, '8.2.0', '>=')) {
            $output->success("PHP " . PHP_VERSION . " (>= 8.2 required)");
        } else {
            $output->error("PHP " . PHP_VERSION . " is unsupported. NEWLA requires PHP 8.2 or higher.");
            $errors++;
        }

        // 2. Required PHP Extensions
        $requiredExtensions = [
            'pdo' => 'Database connectivity (PDO)',
            'openssl' => 'Cryptographic security & token generation',
            'json' => 'JSON serialization and parsing',
            'mbstring' => 'Multibyte string manipulation',
            'fileinfo' => 'MIME type checking and upload validation',
            'gd' => 'Image processing (resize, crop, webp)',
        ];

        foreach ($requiredExtensions as $ext => $label) {
            $checks++;
            if (extension_loaded($ext)) {
                $output->success("Extension [{$ext}]: {$label}");
            } else {
                $output->warning("Extension [{$ext}] is missing: {$label}");
                $warnings++;
            }
        }

        // 3. Database Drivers
        $drivers = PDO::getAvailableDrivers();
        $checks++;
        if (!empty($drivers)) {
            $output->success("PDO Drivers available: " . implode(', ', $drivers));
        } else {
            $output->warning("No PDO drivers found.");
            $warnings++;
        }

        // 4. Project Structure (if inside a project)
        $cwd = $this->getProjectPath();
        if ($this->isInNewlaProject()) {
            $output->writeln();
            $output->writeln($output->color("Project Health:", "1;33"));

            // Check .env
            $checks++;
            if (file_exists($cwd . '/.env')) {
                $output->success(".env configuration file exists");
            } else {
                $output->warning(".env file missing. Run 'cp .env.example .env'");
                $warnings++;
            }

            // Check storage writable
            $checks++;
            $storage = $cwd . '/storage';
            if (is_dir($storage) && is_writable($storage)) {
                $output->success("Storage directory is writable");
            } else {
                $output->warning("Storage directory ({$storage}) is missing or not writable");
                $warnings++;
            }

            // Check document root
            $checks++;
            if (file_exists($cwd . '/public/index.php')) {
                $output->success("Document root (public/index.php) is present");
            } else {
                $output->error("public/index.php missing");
                $errors++;
            }

            // Check newla.json
            $checks++;
            if (file_exists($cwd . '/newla.json')) {
                $output->success("newla.json manifest is present and valid");
            } else {
                $output->warning("newla.json manifest missing");
                $warnings++;
            }
        }

        $output->writeln();
        if ($errors === 0 && $warnings === 0) {
            $output->writeln($output->color("Everything looks great! System is 100% healthy.", "1;32"));
        } else {
            $output->writeln(sprintf(
                "Doctor completed: %d checks, %s, %s",
                $checks,
                $output->color("{$errors} errors", $errors > 0 ? "31" : "32"),
                $output->color("{$warnings} warnings", $warnings > 0 ? "33" : "32")
            ));
        }

        return $errors > 0 ? 1 : 0;
    }
}