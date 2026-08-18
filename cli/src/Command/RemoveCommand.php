<?php

declare(strict_types=1);

namespace Newla\Cli\Command;

use Newla\Cli\Output\ConsoleOutput;

class RemoveCommand extends Command
{
    protected string $name = 'remove';
    protected string $description = 'Remove a NEWLA package';

    public function execute(array $args, array $options, ConsoleOutput $output): int
    {
        $package = $args[0] ?? null;
        if (!$package) {
            $output->error("Please specify a package name: newla remove <package>");
            return 1;
        }

        $packageName = strtolower(str_replace(['@newla/', 'newla/'], '', $package));
        $cwd = $this->getProjectPath();
        $manifestPath = $cwd . '/newla.json';

        if (!file_exists($manifestPath)) {
            $output->error("newla.json not found in current directory.");
            return 1;
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        if (isset($manifest['packages'][$packageName])) {
            unset($manifest['packages'][$packageName]);
            file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $output->success("Package [@newla/{$packageName}] removed from newla.json");
        } else {
            $output->warning("Package [@newla/{$packageName}] was not installed.");
        }

        return 0;
    }
}