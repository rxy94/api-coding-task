<?php

namespace App\Character\Application;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Domain\Service\CharacterValidator;

class CreateCharacterUseCase
{
    public function __construct(
        private CharacterRepository $repository,
        private CharacterValidator $validator
    ) {
    }

    public function execute(
        string $name,
        string $birthDate,
        string $kingdom,
        int $equipmentId,
        int $factionId,
    ): Character {
        // Validar datos
        $this->validator->validate([
            'name' => $name,
            'birth_date' => $birthDate,
            'kingdom' => $kingdom,
            'equipment_id' => $equipmentId,
            'faction_id' => $factionId,
        ]);
        
        $character = new Character();
        $character->setName($name);
        $character->setBirthDate($birthDate);
        $character->setKingdom($kingdom);
        $character->setEquipmentId($equipmentId);
        $character->setFactionId($factionId);

        $this->repository->save($character);

        return $character;
    }
}