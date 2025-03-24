<?php

namespace App\Faction\Application;

use App\Faction\Domain\FactionRepository;
use App\Faction\Domain\Faction;

class ReadFactionByIdUseCase {

    public function __construct(private FactionRepository $repository)
    {
    }

    public function execute(int $id): Faction
    {
        return $this->repository->find($id);
    }

}