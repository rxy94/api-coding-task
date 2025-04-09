<?php

namespace App\Faction\Application;

use App\Faction\Domain\FactionRepository;
use App\Faction\Domain\Exception\FactionNotFoundException;
class DeleteFactionUseCase
{
    public function __construct(private FactionRepository $repository)
    {
    }

    public function execute(int $id): void
    {
        $faction = $this->repository->findById($id);

        if (!$faction) {
            throw FactionNotFoundException::build();
        }

        $this->repository->delete($faction);
    }
}   