<?php

namespace App\Faction\Application;

use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionRepository;

class ReadFactionByIdUseCase {

    public function __construct(private FactionRepository $repository)
    {
    }

    public function execute(int $id): Faction
    {
        return $this->repository->findById($id);
    }

}