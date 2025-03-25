<?php

namespace App\Equipment\Application;

use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Domain\Equipment;

class ReadEquipmentByIdUseCase
{
    public function __construct(private EquipmentRepository $repository)
    {
    }

    public function execute(int $id): Equipment
    {
        return $this->repository->findById($id);

    }
}