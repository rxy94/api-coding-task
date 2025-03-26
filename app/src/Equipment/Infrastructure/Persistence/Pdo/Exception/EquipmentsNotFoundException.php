<?php

namespace App\Equipment\Infrastructure\Persistence\Pdo\Exception;

class EquipmentsNotFoundException extends \Exception
{
    private const MESSAGE = 'No se encontraron equipamientos';

    public static function build(): static
    {
        return new static(self::MESSAGE);
    }
}