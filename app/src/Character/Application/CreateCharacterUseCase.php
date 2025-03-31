<?php

namespace App\Character\Application;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterFactory;
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
        CreateCharacterUseCaseRequest $request
    ): CreateCharacterUseCaseResponse {
        
        # Validamos los datos
        $this->validator->validate(
            $request->getName(), 
            $request->getBirthDate(), 
            $request->getKingdom(), 
            $request->getEquipmentId(), 
            $request->getFactionId()
        );
        
        # Creamos el personaje
        $character = CharacterFactory::build(
            $request->getName(),
            $request->getBirthDate(),
            $request->getKingdom(),
            $request->getEquipmentId(),
            $request->getFactionId()
        );
        
        # Guardamos el personaje
        $this->repository->save($character);

        return new CreateCharacterUseCaseResponse($character);
    }
}