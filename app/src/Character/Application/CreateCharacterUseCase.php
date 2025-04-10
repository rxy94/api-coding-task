<?php

namespace App\Character\Application;

use App\Character\Domain\CharacterFactory;
use App\Character\Domain\CharacterRepository;

class CreateCharacterUseCase
{
    public function __construct(
        private CharacterRepository $repository
    ) {
    }

    public function execute(
        CreateCharacterUseCaseRequest $request
    ): CreateCharacterUseCaseResponse {
        
        # Creamos el personaje
        $character = CharacterFactory::build(
            $request->getName(),
            $request->getBirthDate(),
            $request->getKingdom(),
            $request->getEquipmentId(),
            $request->getFactionId(),
            $request->getId()
        );
        
        # Guardamos el personaje
        $character = $this->repository->save($character); //Lo guardo en $character para que me devuelva el id.

        return new CreateCharacterUseCaseResponse($character);
    }
}