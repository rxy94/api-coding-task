<?php

namespace App\Faction\Domain\Exception;

class FactionNotFoundException extends \Exception
{
    private const MESSAGE = 'Facción no encontrada';

    public static function build(): self
    {
        return new self(self::MESSAGE);
    }
}
