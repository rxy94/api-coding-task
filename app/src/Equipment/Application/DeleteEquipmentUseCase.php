<?php

namespace App\Equipment\Application;

use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Infrastructure\Persistence\Pdo\Exception\EquipmentNotFoundException;

class DeleteEquipmentUseCase
{
    public function __construct(
        private EquipmentRepository $repository
    ) {
    }

    public function execute(int $id): void
    {
        $equipment = $this->repository->findById($id);

        if (!$equipment) {
            throw EquipmentNotFoundException::build();
        }

        $this->repository->delete($equipment);
    }
}