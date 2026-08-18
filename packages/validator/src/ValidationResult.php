<?php

declare(strict_types=1);

namespace Newla\Validator;

class ValidationResult
{
    protected array $data;
    protected array $rules;
    protected ErrorBag $errors;
    protected array $validatedData = [];

    public function __construct(array $data, array $rules, ErrorBag $errors, array $validatedData)
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->errors = $errors;
        $this->validatedData = $validatedData;
    }

    public function passes(): bool
    {
        return $this->errors->isEmpty();
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function errors(): ErrorBag
    {
        return $this->errors;
    }

    public function validated(): array
    {
        if ($this->fails()) {
            throw new ValidationException($this->errors->all());
        }
        return $this->validatedData;
    }

    public function safe(): array
    {
        return $this->validatedData;
    }
}