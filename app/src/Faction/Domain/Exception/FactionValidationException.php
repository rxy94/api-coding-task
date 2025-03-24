<?php

namespace App\Faction\Domain\Exception;

use App\Shared\Domain\Exception\ValidationExceptionInterface;

class FactionValidationException extends \DomainException implements ValidationExceptionInterface
{
    private array $errors;

    public function __construct(array $errors)
    {
        parent::__construct("Error de validación de la facción");
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
    
}