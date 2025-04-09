<?php

namespace App\Faction\Domain\Exception;

use App\Shared\Domain\Exception\ValidationExceptionInterface;

class FactionValidationException extends \DomainException
{
    private const MESSAGE = "Error de validación de la facción";
    private const FACTION_NAME_ERROR = "El nombre de la facción es requerido";
    private const FACTION_NAME_LENGTH_ERROR = "El nombre de la facción no puede exceder los 100 caracteres";
    private const DESCRIPTION_ERROR = "La descripción es requerida";
    private const DESCRIPTION_LENGTH_ERROR = "La descripción no puede exceder los 1000 caracteres";
    private const ID_NON_POSITIVE = "El ID no puede ser negativo";

    # Patrones de diseño: Constructor Semántico

    private function __construct(string $message = self::MESSAGE)
    {
        parent::__construct($message);
    }

    public static function withFactionNameError(): static
    {
        return new self(self::FACTION_NAME_ERROR);
    }

    public static function withFactionNameLengthError(): static
    {
        return new self(self::FACTION_NAME_LENGTH_ERROR);
    }

    public static function withDescriptionError(): static
    {
        return new self(self::DESCRIPTION_ERROR);
    }
    
    public static function withDescriptionLengthError(): static
    {
        return new self(self::DESCRIPTION_LENGTH_ERROR);
    }

    public static function withIdNonPositive(): static
    {
        return new self(self::ID_NON_POSITIVE);
    }
}