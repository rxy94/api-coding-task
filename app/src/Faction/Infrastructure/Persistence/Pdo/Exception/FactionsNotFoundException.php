<?php

namespace App\Faction\Infrastructure\Persistence\Pdo\Exception;

class FactionsNotFoundException extends \Exception
{
    private const MESSAGE = 'No se encontraron facciones';

    public static function build(): self
    {
        return new self(self::MESSAGE);
    }
}