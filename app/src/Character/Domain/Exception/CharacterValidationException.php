<?php

namespace App\Character\Domain\Exception;

class CharacterValidationException extends \DomainException
{
    private const MESSAGE = "Error de validación del personaje";

    private const NAME_REQUIRED = "El nombre es requerido";
    private const NAME_LENGTH_ERROR = "El nombre no puede exceder los 100 caracteres";

    private const BIRTH_DATE_REQUIRED = "La fecha de nacimiento es requerida";
    private const BIRTH_DATE_FORMAT_ERROR = "La fecha de nacimiento debe tener el formato YYYY-MM-DD";

    private const KINGDOM_REQUIRED = "El reino es requerido";
    private const KINGDOM_LENGTH_ERROR = "El reino no puede exceder los 100 caracteres";

    private const EQUIPMENT_ID_NON_POSITIVE = "El ID del equipamiento debe ser un número positivo mayor que 0";
    private const EQUIPMENT_ID_REQUIRED = "El ID del equipamiento es requerido";

    private const FACTION_ID_NON_POSITIVE = "El ID de la facción debe ser un número positivo mayor que 0";
    private const FACTION_ID_REQUIRED = "El ID de la facción es requerido";

    private const ID_NON_POSITIVE = 'El ID debe ser un número positivo mayor que 0';

    # Patrones de diseño: Constructor Semántico

    private function __construct(string $message = self::MESSAGE)
    {
        parent::__construct($message);
    }

    public static function withNameRequired(): self
    {
        return new self(self::NAME_REQUIRED);
    }

    public static function withNameLengthError(): self
    {
        return new self(self::NAME_LENGTH_ERROR);
    }

    public static function withBirthDateRequired(): self
    {
        return new self(self::BIRTH_DATE_REQUIRED);
    }

    public static function withBirthDateFormatError(): self
    {
        return new self(self::BIRTH_DATE_FORMAT_ERROR);
    }

    public static function withKingdomRequired(): self
    {
        return new self(self::KINGDOM_REQUIRED);
    }

    public static function withKingdomLengthError(): self
    {
        return new self(self::KINGDOM_LENGTH_ERROR);
    }

    public static function withEquipmentIdRequired(): self
    {
        return new self(self::EQUIPMENT_ID_REQUIRED);
    }

    public static function withEquipmentIdNonPositive(): self
    {
        return new self(self::EQUIPMENT_ID_NON_POSITIVE);
    }

    public static function withFactionIdRequired(): self
    {
        return new self(self::FACTION_ID_REQUIRED);
    }

    public static function withFactionIdNonPositive(): self
    {
        return new self(self::FACTION_ID_NON_POSITIVE);
    }

    public static function withIdNonPositive(): self
    {
        return new self(self::ID_NON_POSITIVE);
    }

} 