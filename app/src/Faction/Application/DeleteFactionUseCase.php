<?php

namespace App\Faction\Application;

use App\Faction\Domain\FactionRepository;

class DeleteFactionUseCase
{
    public function __construct(private FactionRepository $repository)
    {
    }

    public function execute(int $id): void
    {
        $faction = $this->repository->findById($id);

        if (!$faction) {
            throw new \Exception("Facción no encontrada con ID: {$id}");
        }

        $this->repository->delete($faction);
    }
}   