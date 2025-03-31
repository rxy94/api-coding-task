<?php

namespace App\Faction\Application;

use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionFactory;
use App\Faction\Domain\FactionRepository;
use App\Faction\Domain\Service\FactionValidator;

class CreateFactionUseCase 
{
    public function __construct(
        private FactionRepository $repository,
        private FactionValidator $validator
    ) {
    }

    public function execute(
        CreateFactionUseCaseRequest $request
    ): CreateFactionUseCaseResponse {
        
        # Validamos datos
        $this->validator->validate(
            $request->getFactionName(),
            $request->getDescription()
        );

        # Creamos la facción
        $faction = FactionFactory::build(
            $request->getFactionName(), 
            $request->getDescription()
        );

        # Guardamos la facción
        $this->repository->save($faction);

        return new CreateFactionUseCaseResponse($faction);

    }
    
}   