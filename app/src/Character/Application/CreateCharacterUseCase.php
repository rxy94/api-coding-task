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
        
        # Validamos los datos
        $this->validator->validate($name, $birthDate, $kingdom, $equipmentId, $factionId);
        
        # Creamos el personaje
        $character = new Character(
            $name,
            $birthDate,
            $kingdom,
            $equipmentId,
            $factionId
        );
        
        # Guardamos el personaje
        return $this->repository->save($character);
    }
}