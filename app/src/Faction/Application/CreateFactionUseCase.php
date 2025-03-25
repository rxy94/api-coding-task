<?php

namespace App\Faction\Application;

use App\Faction\Domain\Faction;
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
        string $faction_name,
        string $description
    ): Faction {
        
        # Validamos datos
        $this->validator->validate($faction_name, $description);

        # Creamos la facción
        $faction = new Faction(
            $faction_name,
            $description
        );

        # Guardamos la facción
        return $this->repository->save($faction);

    }
    
}   