<?php

declare(strict_types=1);

namespace Newla\Core\Service;

use Newla\Core\Application;
use Newla\Core\Container\Container;

abstract class ServiceProvider
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function register(): void
    {
    }

    public function boot(): void
    {
    }
}