<?php

namespace App\Character\Domain\Exception;

class CharacterNotFoundException extends \Exception
{
    private const MESSAGE = 'Personaje no encontrado';

    public static function build(): self
    {
        return new self(self::MESSAGE);
    }
}