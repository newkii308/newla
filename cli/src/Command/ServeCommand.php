<?php

declare(strict_types=1);

namespace Newla\Cli\Command;

class ServeCommand extends DevCommand
{
    protected string $name = 'serve';
    protected string $description = 'Alias for dev command';
}