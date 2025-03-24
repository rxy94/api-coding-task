<?php

namespace App\Character\Domain\Exception;

use App\Shared\Domain\Exception\ValidationExceptionInterface;

class CharacterValidationException extends \DomainException implements ValidationExceptionInterface
{
    private const MESSAGE = "Error de validación del personaje";
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

    public function withBirthDateError(): self
    {
        $this->errors[] = "La fecha de nacimiento es requerida";
        return $this;
    }

    public function withBirthDateFormatError(): self
    {
        $this->errors[] = "La fecha de nacimiento debe tener el formato YYYY-MM-DD";
        return $this;
    }

    public function withKingdomError(): self
    {
        $this->errors[] = "El reino es requerido";
        return $this;
    }

    public function withKingdomLengthError(): self
    {
        $this->errors[] = "El reino no puede exceder los 100 caracteres";
        return $this;
    }

    public function withEquipmentIdError(): self
    {
        $this->errors[] = "El ID del equipamiento debe ser un número positivo";
        return $this;
    }

    public function withFactionIdError(): self
    {
        $this->errors[] = "El ID de la facción debe ser un número positivo";
        return $this;
    }

    public function withCustomError(string $error): self
    {
        $this->errors[] = $error;
        return $this;
    }

    public function build(): self
    {
        return $this;
    }

    public function getErrors(): array
    {
        //dump($this->errors);
        return $this->errors;
    }
} 