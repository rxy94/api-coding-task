<?php

namespace App\Character\Domain\Exception;

class CharacterValidationException extends \DomainException
{
    private array $errors;

    public function __construct(array $errors)
    {
        parent::__construct("Error de validación del personaje");
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
} 