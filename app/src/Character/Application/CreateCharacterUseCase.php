<?php

namespace App\Character\Application;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;

class CreateCharacterUseCase
{
    public function __construct(
        private CharacterRepository $repository,
        private ValidateCharacterUseCase $validator
    ) {
    }

    public function execute(
        string $name,
        string $birthDate,
        string $kingdom,
        int $equipmentId,
        int $factionId,
    ): Character {
        
        # Validamos datos
        $this->validator->execute($name, $birthDate, $kingdom, $equipmentId, $factionId);
        
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