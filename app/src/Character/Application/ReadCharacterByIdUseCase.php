<?php

namespace App\Character\Application;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Domain\Service\CharacterValidator;

class ReadCharacterByIdUseCase {

    public function __construct(
        private CharacterRepository $repository,
        private CharacterValidator $validator
    ) {
    }

    public function execute(int $id): Character
    {
        $character = $this->repository->findById($id);
        
        # Validamos los datos del personaje
        $this->validator->validate(
            $character->getName(),
            $character->getBirthDate(),
            $character->getKingdom(),
            $character->getEquipmentId(),
            $character->getFactionId()
        );

        return $character;
    }

}