<?php

namespace App\Faction\Application;

use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionRepository;

class CreateFactionUseCase {
    public function __construct(
        private FactionRepository $repository,
        private ValidateFactionUseCase $validator
    ) {
    }

    public function execute(
        string $faction_name,
        string $description
    ): Faction {
        
        # Validamos datos
        $this->validator->execute($faction_name, $description);

        $faction = new Faction();
        $faction->setName($faction_name);
        $faction->setDescription($description);

        $this->repository->save($faction);

        return $faction;
    }
    
    
}   