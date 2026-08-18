<?php

declare(strict_types=1);

namespace Newla\Cli\Command;

use Newla\Cli\Output\ConsoleOutput;

class ListCommand extends Command
{
    protected string $name = 'list';
    protected string $description = 'List all available commands or packages';
    protected array $commands;

    public function __construct(array $commands = [])
    {
        $this->commands = $commands;
    }

    public function execute(array $args, array $options, ConsoleOutput $output): int
    {
        $sub = $args[0] ?? null;

        if ($sub === 'packages' || isset($options['packages'])) {
            $output->title("NEWLA Modular Packages");
            $pkgs = [
                '@newla/core' => 'Core framework, router, container, database, HTTP',
                '@newla/security' => 'CSRF, Password Hashing, Rate Limiting, Security Headers',
                '@newla/validator' => 'Data validation and custom validation rules',
                '@newla/logger' => 'Multi-channel logging (file, stderr, db, webhook)',
                '@newla/storage' => 'File storage abstraction (Local, S3, Cloudflare R2)',
                '@newla/image' => 'Image processing, WebP conversion, thumbnailing',
                '@newla/auth' => 'Authentication, session guards, password reset',
                '@newla/api' => 'Standard JSON API responses, pagination, error formatting',
            ];

            foreach ($pkgs as $name => $desc) {
                $output->writeln(sprintf("  \033[32m%-20s\033[0m %s", $name, $desc));
            }
            return 0;
        }

        $output->banner();
        $output->writeln($output->color("Available Commands:", "1;33"));
        $output->writeln();

        foreach ($this->commands as $name => $cmd) {
            $output->writeln(sprintf("  \033[32m%-22s\033[0m %s", $name, $cmd->getDescription()));
        }

        return 0;
    }
}