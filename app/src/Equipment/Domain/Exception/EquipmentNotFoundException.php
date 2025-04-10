<?php

namespace App\Equipment\Domain\Exception;

class EquipmentNotFoundException extends \Exception
{
    private const MESSAGE = 'Equipo no encontrado';

    public static function build(): self
    {
        return new self(self::MESSAGE);
    }
}