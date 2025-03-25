<?php

namespace App\Equipment\Domain\Exception;

use App\Shared\Domain\Exception\ValidationExceptionInterface;

class EquipmentValidationException extends \DomainException implements ValidationExceptionInterface
{
    private const MESSAGE = "Error de validación del equipamiento";
    private array $errors = [];

    private function __construct()
    {
        parent::__construct(self::MESSAGE);
    }

    public static function builder(): self
    {
        return new self();
    }

    public function withNameError(): self
    {
        $this->errors[] = "El nombre es requerido";
        return $this;
    }

    public function withNameLengthError(): self
    {
        $this->errors[] = "El nombre no puede exceder los 100 caracteres";
        return $this;
    }

    public function withTypeError(): self
    {
        $this->errors[] = "El tipo es requerido";
        return $this;
    }

    public function withMadeByError(): self
    {
        $this->errors[] = "El fabricante es requerido";
        return $this;
    }

    public function withMadeByLengthError(): self
    {
        $this->errors[] = "El fabricante no puede exceder los 100 caracteres";
        return $this;
    }
    
    public function build(): self
    {
        return $this;
    }

    public function getErrors(): array  
    {
        return $this->errors;
    }

}