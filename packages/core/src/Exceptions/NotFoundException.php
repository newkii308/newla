<?php

declare(strict_types=1);

namespace Newla\Core\Exceptions;

class NotFoundException extends HttpException
{
    public function __construct(string $message = '404 Not Found', ?\Throwable $previous = null)
    {
        parent::__construct(404, $message, $previous);
    }
}