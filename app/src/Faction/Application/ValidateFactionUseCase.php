<?php

namespace App\Faction\Application;

use App\Faction\Domain\Service\FactionValidator;

class ValidateFactionUseCase
{
    public function __construct(private FactionValidator $validator)
    {
    }

    public function execute(
        string $factionName,
        string $description,
    ): void {
        $this->validator->validate([
            'faction_name' => $factionName,
            'description' => $description,
        ]);
    }
    
}
