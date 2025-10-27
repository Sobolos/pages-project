<?php
namespace App\Infrastructure\Services;

class Validator
{
    public function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleSet) {
            foreach ($ruleSet as $rule) {
                $value = $data[$field] ?? null;
                if ($rule === 'required' && (is_null($value) || $value === '')) {
                    $errors[$field][] = "$field is required";
                }
                if ($rule === 'email' && $value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = "$field must be a valid email address";
                }
                if ($rule === 'hex_color' && $value && !preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
                    $errors[$field][] = "$field must be a valid HEX color (e.g., #FFFFFF)";
                }
                if ($rule === 'rating' && $value !== null && ($value < 0 || $value > 5)) {
                    $errors[$field][] = "$field must be between 0 and 5";
                }
                if ($rule === 'positive_int' && $value !== null && (!is_int($value) || $value < 0)) {
                    $errors[$field][] = "$field must be a positive integer";
                }
                if ($rule === 'string' && $value !== null && !is_string($value)) {
                    $errors[$field][] = "$field must be a string";
                }
                if ($rule === 'boolean' && $value !== null && !is_bool($value)) {
                    $errors[$field][] = "$field must be a boolean";
                }
                if ($rule === 'array' && $value !== null && !is_array($value)) {
                    $errors[$field][] = "$field must be an array";
                }
            }
        }

        return $errors;
    }
}