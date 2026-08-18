<?php

declare(strict_types=1);

/**
 * NEWLA — The Native PHP Framework
 * Entry point for web requests.
 */

define('NEWLA_START', microtime(true));

/** @var \Newla\Core\Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->run();
