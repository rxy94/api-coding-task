<?php

namespace App\Equipment\Domain\Exception;

class EquipmentValidationException extends \DomainException
{
    private const MESSAGE = "Error de validación del equipamiento";

    private const NAME_ERROR = "El nombre es requerido";
    private const NAME_LENGTH_ERROR = "El nombre no puede exceder los 100 caracteres";

    private const TYPE_ERROR = "El tipo es requerido";
    private const TYPE_LENGTH_ERROR = "El tipo no puede exceder los 100 caracteres";

    private const MADE_BY_ERROR = "El fabricante es requerido";
    private const MADE_BY_LENGTH_ERROR = "El fabricante no puede exceder los 100 caracteres";
    
    private const ID_NON_POSITIVE = "El ID debe ser un número positivo mayor que 0";

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

    public static function withTypeError(): static
    {
        return new self(self::TYPE_ERROR);
    }

    public static function withTypeErrorLengthError(): static
    {
        return new self(self::TYPE_LENGTH_ERROR);
    }

    public static function withMadeByError(): static
    {
        return new self(self::MADE_BY_ERROR);
    }

    public static function withMadeByLengthError(): static
    {
        return new self(self::MADE_BY_LENGTH_ERROR);
    }

    public static function withIdNonPositive(): static
    {
        return new self(self::ID_NON_POSITIVE);
    }

}