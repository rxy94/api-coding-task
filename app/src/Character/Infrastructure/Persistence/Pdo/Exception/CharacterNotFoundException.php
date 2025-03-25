<?php

namespace App\Character\Infrastructure\Persistence\Pdo\Exception;

class CharacterNotFoundException extends \Exception
{
    private const MESSAGE = "Personaje no encontrado";

    public static function build(): static
    {
        return new static(self::MESSAGE);
    }
}
