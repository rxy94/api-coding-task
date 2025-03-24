<?php

namespace App\Character\Domain\Exception;

class CharacterValidationException extends \DomainException
{
    private const MESSAGE = "Error de validación del personaje";

    private function __construct(private array $errors)
    {
        parent::__construct(self::MESSAGE);
        $this->errors = $errors;
    }

    public static function fromErrors(array $errors): self
    {
        return new self($errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
} 