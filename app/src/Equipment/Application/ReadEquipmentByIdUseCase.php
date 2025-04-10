<?php

namespace App\Equipment\Application;

use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\Exception\EquipmentNotFoundException;

class ReadEquipmentByIdUseCase
{
    public function __construct(
        private EquipmentRepository $repository
    ) {
    }

    public function execute(int $id): Equipment
    {
        $equipment = $this->repository->findById($id);

        if (!$equipment) {
            throw EquipmentNotFoundException::build();
        }

        return $equipment;
    }
}