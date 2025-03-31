<?php

namespace App\Character\Application;

use App\Character\Domain\Character;

class CreateCharacterUseCaseResponse
{
    public function __construct(
        private readonly Character $character,
    ) {
    }

    public function getCharacter(): Character
    {
        return $this->character;
    }
}
