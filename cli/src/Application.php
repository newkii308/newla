<?php

declare(strict_types=1);

namespace Newla\Cli;

use Newla\Cli\Command\CommandInterface;
use Newla\Cli\Output\ConsoleOutput;

class Application
{
    public const VERSION = '1.0.1';

    /** @var array<string, CommandInterface> */
    protected array $commands = [];
    protected ConsoleOutput $output;

    public function __construct()
    {
        $this->output = new ConsoleOutput();
        $this->registerDefaultCommands();
    }

    public function register(CommandInterface $command): void
    {
        $this->commands[$command->getName()] = $command;
    }

    protected function registerDefaultCommands(): void
    {
        $this->register(new Command\CreateCommand());
        $this->register(new Command\InitCommand());
        $this->register(new Command\InfoCommand());
        $this->register(new Command\DoctorCommand());
        $this->register(new Command\AddCommand());
        $this->register(new Command\RemoveCommand());
        $this->register(new Command\UpdateCommand());
        $this->register(new Command\SelfUpdateCommand());
        $this->register(new Command\ListCommand($this->commands));
        $this->register(new Command\DevCommand());
        $this->register(new Command\ServeCommand());
        $this->register(new Command\TestCommand());
        $this->register(new Command\MakeControllerCommand());
        $this->register(new Command\MakeModelCommand());
        $this->register(new Command\MakeMiddlewareCommand());
        $this->register(new Command\MakeServiceCommand());
        $this->register(new Command\MakeMigrationCommand());
        $this->register(new Command\MakeSeederCommand());
        $this->register(new Command\MigrateCommand());
        $this->register(new Command\MigrateRollbackCommand());
        $this->register(new Command\MigrateFreshCommand());
        $this->register(new Command\DbSeedCommand());
        $this->register(new Command\CacheClearCommand());
        $this->register(new Command\BuildCommand());
    }

    public function run(array $argv): int
    {
        array_shift($argv); // Remove script name

        if (empty($argv) || in_array($argv[0], ['-h', '--help', 'help'], true)) {
            $this->output->banner();
            $this->showHelp();
            return 0;
        }

        if (in_array($argv[0], ['-v', '--version', 'version'], true)) {
            $this->output->writeln("NEWLA CLI " . self::VERSION);
            $this->output->writeln("PHP " . PHP_VERSION);
            $this->output->writeln("Platform: " . PHP_OS_FAMILY . " (" . PHP_OS . ")");
            return 0;
        }

        $commandName = array_shift($argv);
        $args = [];
        $options = [];

        foreach ($argv as $arg) {
            if (str_starts_with($arg, '--')) {
                $opt = substr($arg, 2);
                if (str_contains($opt, '=')) {
                    [$key, $val] = explode('=', $opt, 2);
                    $options[$key] = $val;
                } else {
                    $options[$opt] = true;
                }
            } elseif (str_starts_with($arg, '-')) {
                $options[substr($arg, 1)] = true;
            } else {
                $args[] = $arg;
            }
        }

        if (!isset($this->commands[$commandName])) {
            $this->output->error("Unknown command: [{$commandName}]");
            $this->output->writeln("Run 'newla help' or 'newla list' to see all available commands.");
            return 1;
        }

        return $this->commands[$commandName]->execute($args, $options, $this->output);
    }

    public function showHelp(): void
    {
        $this->output->writeln($this->output->color("Usage:", "1;33"));
        $this->output->writeln("  newla <command> [arguments] [options]");
        $this->output->writeln();
        $this->output->writeln($this->output->color("Available commands:", "1;33"));

        $grouped = [
            'Project' => ['create', 'init', 'info', 'doctor'],
            'Packages' => ['add', 'remove', 'update', 'list'],
            'Development' => ['dev', 'serve', 'test'],
            'Generators' => ['make:controller', 'make:model', 'make:middleware', 'make:service', 'make:migration', 'make:seeder'],
            'Database' => ['migrate', 'migrate:rollback', 'migrate:fresh', 'db:seed'],
            'Cache & Build' => ['cache:clear', 'build'],
        ];

        foreach ($grouped as $category => $cmds) {
            $this->output->writeln($this->output->color(" {$category}:", "1;34"));
            foreach ($cmds as $name) {
                if (isset($this->commands[$name])) {
                    $desc = $this->commands[$name]->getDescription();
                    $this->output->writeln(sprintf("  \033[32m%-22s\033[0m %s", $name, $desc));
                }
            }
            $this->output->writeln();
        }
    }

    public function getCommands(): array
    {
        return $this->commands;
    }
}