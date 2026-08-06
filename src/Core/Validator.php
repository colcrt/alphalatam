<?php

declare(strict_types=1);

namespace App\Core;

class Validator
{
    private array $errors = [];
    private array $data;

    private function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function make(array $data, array $rules): self
    {
        $validator = new self($data);
        foreach ($rules as $field => $ruleString) {
            $rulesList = is_string($ruleString) ? explode('|', $ruleString) : $ruleString;
            $validator->validateField($field, $rulesList);
        }
        return $validator;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0];
        }
        return null;
    }

    public function validated(): array
    {
        return $this->data;
    }

    private function validateField(string $field, array $rules): void
    {
        $value = $this->data[$field] ?? null;

        foreach ($rules as $rule) {
            $params = [];
            if (str_contains($rule, ':')) {
                [$ruleName, $paramStr] = explode(':', $rule, 2);
                $params = explode(',', $paramStr);
                $rule = $ruleName;
            }

            switch ($rule) {
                case 'required':
                    if ($value === null || $value === '') {
                        $this->errors[$field][] = "El campo {$field} es obligatorio.";
                    }
                    break;
                case 'string':
                    if ($value !== null && !is_string($value)) {
                        $this->errors[$field][] = "El campo {$field} debe ser texto.";
                    }
                    break;
                case 'email':
                    if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $this->errors[$field][] = "El campo {$field} debe ser un email válido.";
                    }
                    break;
                case 'numeric':
                    if ($value !== null && $value !== '' && !is_numeric($value)) {
                        $this->errors[$field][] = "El campo {$field} debe ser numérico.";
                    }
                    break;
                case 'min':
                    $min = (int) ($params[0] ?? 0);
                    if (is_string($value) && mb_strlen($value) < $min) {
                        $this->errors[$field][] = "El campo {$field} debe tener al menos {$min} caracteres.";
                    }
                    break;
                case 'max':
                    $max = (int) ($params[0] ?? 255);
                    if (is_string($value) && mb_strlen($value) > $max) {
                        $this->errors[$field][] = "El campo {$field} no debe exceder {$max} caracteres.";
                    }
                    break;
                case 'size':
                    $size = (int) ($params[0] ?? 0);
                    if (is_string($value) && mb_strlen($value) !== $size) {
                        $this->errors[$field][] = "El campo {$field} debe tener {$size} caracteres.";
                    }
                    break;
                case 'nullable':
                    break;
                case 'boolean':
                    if ($value !== null && !in_array($value, [0, 1, '0', '1', true, false])) {
                        $this->errors[$field][] = "El campo {$field} debe ser verdadero o falso.";
                    }
                    break;
                case 'date':
                    if ($value !== null && $value !== '' && !strtotime($value)) {
                        $this->errors[$field][] = "El campo {$field} debe ser una fecha válida.";
                    }
                    break;
                case 'in':
                    if ($value !== null && !in_array($value, $params)) {
                        $this->errors[$field][] = "El campo {$field} debe ser uno de: " . implode(', ', $params) . ".";
                    }
                    break;
            }
        }
    }

}
