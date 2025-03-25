<?php

namespace App\Character\Domain\Exception;

class CharacterValidationException extends \DomainException
{
    private const MESSAGE = "Error de validación del personaje";
    private const NAME_ERROR = "El nombre es requerido";
    private const NAME_LENGTH_ERROR = "El nombre no puede exceder los 100 caracteres";
    private const BIRTH_DATE_ERROR = "La fecha de nacimiento es requerida";
    private const BIRTH_DATE_FORMAT_ERROR = "La fecha de nacimiento debe tener el formato YYYY-MM-DD";
    private const KINGDOM_ERROR = "El reino es requerido";
    private const KINGDOM_LENGTH_ERROR = "El reino no puede exceder los 100 caracteres";
    private const EQUIPMENT_ID_ERROR = "El ID del equipamiento debe ser un número positivo";
    private const EQUIPMENT_ID_TYPE_ERROR = "El ID del equipamiento debe ser un número entero";
    private const FACTION_ID_ERROR = "El ID de la facción debe ser un número positivo";
    private const FACTION_ID_TYPE_ERROR = "El ID de la facción debe ser un número entero";

    # Patrones de diseño: Constructor Semántico

    private function __construct(string $message = self::MESSAGE)
    {
        parent::__construct($message);
    }

    public static function withNameError(): static
    {
        return new self(self::NAME_ERROR);
    }

    public static function withNameLengthError(): static
    {
        return new self(self::NAME_LENGTH_ERROR);
    }

    public static function withBirthDateError(): static
    {
        return new self(self::BIRTH_DATE_ERROR);
    }

    public static function withBirthDateFormatError(): static
    {
        return new self(self::BIRTH_DATE_FORMAT_ERROR);
    }

    public static function withKingdomError(): static
    {
        return new self(self::KINGDOM_ERROR);
    }

    public static function withKingdomLengthError(): static
    {
        return new self(self::KINGDOM_LENGTH_ERROR);
    }

    public static function withEquipmentIdError(): static
    {
        return new self(self::EQUIPMENT_ID_ERROR);
    }

    public static function withEquipmentIdTypeError(): static
    {
        return new self(self::EQUIPMENT_ID_TYPE_ERROR);
    }

    public static function withFactionIdError(): static
    {
        return new self(self::FACTION_ID_ERROR);
    }

    public static function withFactionIdTypeError(): static
    {
        return new self(self::FACTION_ID_TYPE_ERROR);
    }

} 