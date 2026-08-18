<?php

declare(strict_types=1);

namespace Newla\Cli\Command;

use Newla\Cli\Output\ConsoleOutput;

class CacheClearCommand extends Command
{
    protected string $name = 'cache:clear';
    protected string $description = 'Clear application cache files';

    public function execute(array $args, array $options, ConsoleOutput $output): int
    {
        $cwd = $this->getProjectPath();
        $cacheDir = $cwd . '/storage/cache';

        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }

        $output->success("Application cache cleared successfully.");
        return 0;
    }
}