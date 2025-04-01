<?php

namespace App\Character\Application;

use App\Character\Domain\Character;

class UpdateCharacterUseCaseResponse
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