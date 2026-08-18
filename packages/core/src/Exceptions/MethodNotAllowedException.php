<?php

declare(strict_types=1);

namespace Newla\Core\Exceptions;

class MethodNotAllowedException extends HttpException
{
    public function __construct(string $message = '405 Method Not Allowed', ?\Throwable $previous = null)
    {
        parent::__construct(405, $message, $previous);
    }
}