<?php

namespace App\Character\Domain\Exception;

class CharacterNotFoundException extends \Exception
{
    private const MESSAGE = 'Character not found';

    public static function build(): self
    {
        return new self(self::MESSAGE);
    }
}