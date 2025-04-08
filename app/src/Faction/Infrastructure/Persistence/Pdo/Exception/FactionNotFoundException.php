<?php

namespace App\Faction\Infrastructure\Persistence\Pdo\Exception;

class FactionNotFoundException extends \Exception
{
    private const MESSAGE = 'Facción no encontrada';

    public static function build(): static
    {
        return new static(self::MESSAGE);
    }
}