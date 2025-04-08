<?php

namespace App\Faction\Application;

use App\Faction\Domain\FactionFactory;
use App\Faction\Domain\FactionRepository;

class CreateFactionUseCase 
{
    public function __construct(
        private FactionRepository $repository
    ) {
    }

    public function execute(
        CreateFactionUseCaseRequest $request
    ): CreateFactionUseCaseResponse {

        # Creamos la facción
        $faction = FactionFactory::build(
            $request->getFactionName(), 
            $request->getDescription(),
            $request->getId()
        );

        # Guardamos la facción
        $faction = $this->repository->save($faction);

        return new CreateFactionUseCaseResponse($faction);

    }
    
}   