<?php

namespace App\Faction\Application;

use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionRepository;
use App\Faction\Domain\Exception\FactionNotFoundException;

class ReadFactionByIdUseCase {

    public function __construct(
        private FactionRepository $repository
    ) {
    }

    public function execute(int $id): Faction
    {
        $faction = $this->repository->findById($id);

        if (!$faction) {
            throw FactionNotFoundException::build();
        }

        return $faction;
    }

}