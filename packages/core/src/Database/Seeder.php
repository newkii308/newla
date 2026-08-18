<?php

declare(strict_types=1);

namespace Newla\Core\Database;

abstract class Seeder
{
    abstract public function run(): void;

    public function call(string $class): void
    {
        $seeder = new $class();
        $seeder->run();
    }
}