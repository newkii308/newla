<?php

declare(strict_types=1);

namespace Newla\Cli\Command;

use Newla\Cli\Output\ConsoleOutput;

class AddCommand extends Command
{
    protected string $name = 'add';
    protected string $description = 'Install a NEWLA package (e.g., newla add security)';

    public function execute(array $args, array $options, ConsoleOutput $output): int
    {
        $package = $args[0] ?? null;
        if (!$package) {
            $output->error("Please specify a package name: newla add <package>");
            $output->writeln("Available packages: security, validator, logger, storage, image, auth, api");
            return 1;
        }

        $packageName = strtolower(str_replace('@newla/', '', $package));
        $packageName = str_replace('newla/', '', $packageName);

        $validPackages = ['security', 'validator', 'logger', 'storage', 'image', 'auth', 'api', 'core'];
        if (!in_array($packageName, $validPackages, true)) {
            $output->error("Unknown package: [{$packageName}]. Available packages: " . implode(', ', $validPackages));
            return 1;
        }

        $output->writeln($output->color("Adding package [@newla/{$packageName}]...", "1;36"));

        $cwd = $this->getProjectPath();
        $manifestPath = $cwd . '/newla.json';

        $manifest = file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [
            'name' => basename($cwd),
            'version' => '1.0.0',
            'framework' => 'newla',
            'packages' => []
        ];

        $manifest['packages'][$packageName] = '^1.0';
        file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $output->success("Package [@newla/{$packageName}] registered in newla.json");
        $output->writeln();
        $output->writeln($output->color("Package installed successfully!", "1;32"));

        return 0;
    }
}