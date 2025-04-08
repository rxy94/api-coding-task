<?php

namespace App\Faction\Application;

use App\Faction\Domain\Faction;

class UpdateFactionUseCaseResponse
{
    public function __construct(
        private readonly Faction $faction,
    ) {
    }

    public function getFaction(): Faction
    {
        return $this->faction;
    }
}