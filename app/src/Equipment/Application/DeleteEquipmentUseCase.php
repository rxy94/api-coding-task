<?php

namespace App\Equipment\Application;

use App\Equipment\Domain\EquipmentRepository;

class DeleteEquipmentUseCase
{
    public function __construct(private EquipmentRepository $repository)
    {
    }

    public function execute(int $id): void
    {
        $equipment = $this->repository->findById($id);

        if (!$equipment) {
            throw new \Exception("Equipo no encontrado con ID: {$id}");
        }

        $this->repository->delete($equipment);
    }
}