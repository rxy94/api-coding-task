<?php

namespace App\Character\Infrastructure\Persistence\Pdo\Exception;

class CharactersNotFoundException extends \Exception
{
    public static function build(): static
    {
        return new static('Error al obtener los personajes');
    }
}