<?php

namespace App\Equipment\Infrastructure\Persistence\Pdo\Exception;

class EquipmentNotFoundException extends \Exception
{
    public const MESSAGE = 'Equipamiento no encontrado';

    public static function build(): static
    {
        return new static(self::MESSAGE);
    }

}