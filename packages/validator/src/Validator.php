<?php

declare(strict_types=1);

namespace Newla\Validator;

use Closure;
use Newla\Core\Container\Container;
use Newla\Core\Database\Connection;

class Validator
{
    protected static array $customRules = [];
    protected static array $customMessages = [];

    public static function make(array $data, array $rules, array $customMessages = []): ValidationResult
    {
        $validator = new static();
        return $validator->validate($data, $rules, $customMessages);
    }

    public static function extend(string $rule, Closure|RuleInterface $handler, ?string $message = null): void
    {
        static::$customRules[$rule] = $handler;
        if ($message) {
            static::$customMessages[$rule] = $message;
        }
    }

    public function validate(array $data, array $rules, array $customMessages = []): ValidationResult
    {
        $errors = new ErrorBag();
        $validatedData = [];

        foreach ($rules as $attribute => $ruleSet) {
            $parsedRules = $this->parseRules($ruleSet);
            $value = $data[$attribute] ?? null;

            $isNullable = in_array('nullable', array_column($parsedRules, 'name'), true);
            $isRequired = in_array('required', array_column($parsedRules, 'name'), true);

            if ($value === null || $value === '') {
                if ($isNullable) {
                    $validatedData[$attribute] = null;
                    continue;
                }
                if ($isRequired) {
                    $errors->add($attribute, $this->formatMessage($attribute, 'required', [], $customMessages));
                }
                continue;
            }

            $attributePassed = true;

            foreach ($parsedRules as $rule) {
                $ruleName = $rule['name'];
                $params = $rule['parameters'];

                if (in_array($ruleName, ['required', 'nullable'], true)) {
                    continue;
                }

                $passed = $this->checkRule($ruleName, $attribute, $value, $params, $data);

                if (!$passed) {
                    $errors->add($attribute, $this->formatMessage($attribute, $ruleName, $params, $customMessages));
                    $attributePassed = false;
                    break; // Stop at first failing rule for this attribute
                }
            }

            if ($attributePassed) {
                $validatedData[$attribute] = $value;
            }
        }

        return new ValidationResult($data, $rules, $errors, $validatedData);
    }

    protected function checkRule(string $rule, string $attribute, mixed $value, array $params, array $data): bool
    {
        if (isset(static::$customRules[$rule])) {
            $handler = static::$customRules[$rule];
            if ($handler instanceof RuleInterface) {
                return $handler->passes($attribute, $value, $params, $data);
            }
            return $handler($attribute, $value, $params, $data);
        }

        return match ($rule) {
            'string' => is_string($value),
            'integer', 'int' => filter_var($value, FILTER_VALIDATE_INT) !== false,
            'numeric' => is_numeric($value),
            'boolean', 'bool' => is_bool($value) || in_array($value, [1, 0, '1', '0', 'true', 'false', true, false], true),
            'email' => is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'url' => is_string($value) && filter_var($value, FILTER_VALIDATE_URL) !== false,
            'min' => $this->validateMin($value, (float) ($params[0] ?? 0)),
            'max' => $this->validateMax($value, (float) ($params[0] ?? 0)),
            'length' => $this->validateLength($value, $params),
            'regex' => is_string($value) && @preg_match($params[0] ?? '//', $value) === 1,
            'in' => in_array((string) $value, array_map('strval', $params), true),
            'not_in' => !in_array((string) $value, array_map('strval', $params), true),
            'confirmed' => isset($data[$attribute . '_confirmation']) && $value === $data[$attribute . '_confirmation'],
            'unique' => $this->validateUnique($attribute, $value, $params),
            'array' => is_array($value),
            'json' => is_string($value) && json_decode($value) !== null && json_last_error() === JSON_ERROR_NONE,
            default => true,
        };
    }

    protected function validateMin(mixed $value, float $min): bool
    {
        if (is_numeric($value)) {
            return (float) $value >= $min;
        }
        if (is_string($value)) {
            return mb_strlen($value) >= (int) $min;
        }
        if (is_array($value)) {
            return count($value) >= (int) $min;
        }
        return false;
    }

    protected function validateMax(mixed $value, float $max): bool
    {
        if (is_numeric($value)) {
            return (float) $value <= $max;
        }
        if (is_string($value)) {
            return mb_strlen($value) <= (int) $max;
        }
        if (is_array($value)) {
            return count($value) <= (int) $max;
        }
        return false;
    }

    protected function validateLength(mixed $value, array $params): bool
    {
        $len = is_string($value) ? mb_strlen($value) : (is_array($value) ? count($value) : 0);
        if (count($params) === 1) {
            return $len === (int) $params[0];
        }
        if (count($params) >= 2) {
            return $len >= (int) $params[0] && $len <= (int) $params[1];
        }
        return true;
    }

    protected function validateUnique(string $attribute, mixed $value, array $params): bool
    {
        $table = $params[0] ?? $attribute;
        $column = $params[1] ?? $attribute;
        $exceptId = $params[2] ?? null;
        $idColumn = $params[3] ?? 'id';

        if (!Container::getInstance()->has('db')) {
            return true;
        }

        /** @var Connection $conn */
        $conn = Container::getInstance()->make('db')->connection();
        $query = $conn->table($table)->where($column, $value);

        if ($exceptId !== null && $exceptId !== 'NULL') {
            $query->where($idColumn, '!=', $exceptId);
        }

        return $query->count() === 0;
    }

    protected function parseRules(string|array $rules): array
    {
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }

        $parsed = [];
        foreach ($rules as $rule) {
            if ($rule instanceof RuleInterface || $rule instanceof Closure) {
                $name = 'custom_' . spl_object_id($rule);
                static::$customRules[$name] = $rule;
                $parsed[] = ['name' => $name, 'parameters' => []];
                continue;
            }

            if (str_contains($rule, ':')) {
                [$name, $paramStr] = explode(':', $rule, 2);
                $params = str_getcsv($paramStr, escape: '\\');
            } else {
                $name = $rule;
                $params = [];
            }

            $parsed[] = ['name' => strtolower(trim($name)), 'parameters' => $params];
        }

        return $parsed;
    }

    protected function formatMessage(string $attribute, string $rule, array $params, array $customMessages): string
    {
        $customKey = "{$attribute}.{$rule}";
        if (isset($customMessages[$customKey])) {
            return $this->replacePlaceholders($customMessages[$customKey], $attribute, $params);
        }

        if (isset($customMessages[$rule])) {
            return $this->replacePlaceholders($customMessages[$rule], $attribute, $params);
        }

        if (isset(static::$customMessages[$rule])) {
            return $this->replacePlaceholders(static::$customMessages[$rule], $attribute, $params);
        }

        $defaultMessages = [
            'required' => "The :attribute field is required.",
            'string' => "The :attribute field must be a string.",
            'integer' => "The :attribute field must be an integer.",
            'numeric' => "The :attribute field must be a number.",
            'boolean' => "The :attribute field must be true or false.",
            'email' => "The :attribute field must be a valid email address.",
            'url' => "The :attribute field must be a valid URL.",
            'min' => "The :attribute field must be at least :param0.",
            'max' => "The :attribute field must not exceed :param0.",
            'length' => "The :attribute field length is invalid.",
            'regex' => "The :attribute format is invalid.",
            'in' => "The selected :attribute is invalid.",
            'not_in' => "The selected :attribute is invalid.",
            'confirmed' => "The :attribute confirmation does not match.",
            'unique' => "The :attribute has already been taken.",
            'array' => "The :attribute field must be an array.",
            'json' => "The :attribute field must be a valid JSON string.",
        ];

        $template = $defaultMessages[$rule] ?? "The :attribute field is invalid.";
        return $this->replacePlaceholders($template, $attribute, $params);
    }

    protected function replacePlaceholders(string $template, string $attribute, array $params): string
    {
        $readableAttr = str_replace('_', ' ', $attribute);
        $message = str_replace(':attribute', $readableAttr, $template);

        foreach ($params as $index => $param) {
            $message = str_replace(":param{$index}", $param, $message);
        }

        return $message;
    }
}