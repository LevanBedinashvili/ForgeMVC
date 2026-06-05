<?php

declare(strict_types=1);

namespace Core;

class Validator
{
    /**
     * Collected validation errors keyed by field name.
     */
    protected static array $errors = [];

    /**
     * Validate an array of data against a set of rules.
     *
     * Usage:
     *   $errors = Validator::validate($data, [
     *       'name'  => 'required|min:2|max:255',
     *       'email' => 'required|email',
     *   ]);
     *
     * Returns an empty array when validation passes.
     */
    public static function validate(array $data, array $rules): array
    {
        static::$errors = [];

        foreach ($rules as $field => $ruleString) {
            $fieldRules = explode('|', $ruleString);
            $value = $data[$field] ?? null;

            foreach ($fieldRules as $rule) {
                static::applyRule($field, $value, $rule);
            }
        }

        return static::$errors;
    }

    /**
     * Apply a single rule to a field value.
     */
    protected static function applyRule(string $field, mixed $value, string $rule): void
    {
        // Parse rule name and optional parameter (e.g., "min:3")
        $parts = explode(':', $rule, 2);
        $ruleName = $parts[0];
        $param = $parts[1] ?? null;

        match ($ruleName) {
            'required' => static::validateRequired($field, $value),
            'email'    => static::validateEmail($field, $value),
            'min'      => static::validateMin($field, $value, (int) $param),
            'max'      => static::validateMax($field, $value, (int) $param),
            default    => throw new \InvalidArgumentException("Unknown validation rule: {$ruleName}"),
        };
    }

    /**
     * The field must be present and not empty.
     */
    protected static function validateRequired(string $field, mixed $value): void
    {
        if ($value === null || $value === '' || $value === []) {
            static::addError($field, "The {$field} field is required.");
        }
    }

    /**
     * The field must be a valid email address.
     */
    protected static function validateEmail(string $field, mixed $value): void
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            static::addError($field, "The {$field} field must be a valid email address.");
        }
    }

    /**
     * The field must have a minimum string length.
     */
    protected static function validateMin(string $field, mixed $value, int $min): void
    {
        if ($value !== null && $value !== '' && mb_strlen((string) $value) < $min) {
            static::addError($field, "The {$field} field must be at least {$min} characters.");
        }
    }

    /**
     * The field must not exceed a maximum string length.
     */
    protected static function validateMax(string $field, mixed $value, int $max): void
    {
        if ($value !== null && $value !== '' && mb_strlen((string) $value) > $max) {
            static::addError($field, "The {$field} field must not exceed {$max} characters.");
        }
    }

    /**
     * Add an error message for a field.
     */
    protected static function addError(string $field, string $message): void
    {
        static::$errors[$field][] = $message;
    }
}
