<?php

namespace App\Character\Application;

use App\Character\Domain\Service\CharacterValidator;

class ValidateCharacterUseCase
{
    public function __construct(private CharacterValidator $validator)
    {
    }

    public function execute(
        string $name,
        string $birthDate,
        string $kingdom,
        int $equipmentId,
        int $factionId,
    ): void {
        $this->validator->validate([
            'name' => $name,
            'birth_date' => $birthDate,
            'kingdom' => $kingdom,
            'equipment_id' => $equipmentId,
            'faction_id' => $factionId,
        ]);
    }
}
