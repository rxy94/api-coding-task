<?php

namespace App\Faction\Infrastructure\Persistence\Pdo\Exception;

class FactionNotFoundException extends \Exception
{
    private const MESSAGE = 'La facción no encontrada';

    public static function build(): self
    {
        return new self(self::MESSAGE);
    }
}