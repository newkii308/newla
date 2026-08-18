<?php

declare(strict_types=1);

namespace Newla\Validator;

use Exception;

class ValidationException extends Exception
{
    protected array $errors = [];

    public function __construct(array $errors = [], string $message = 'The given data failed validation.')
    {
        $this->errors = $errors;
        parent::__construct($message, 422);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}