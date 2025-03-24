<?php

namespace App\Equipment\Application;

use App\Equipment\Domain\EquipmentRepository;

class ReadEquipmentUseCase {

    public function __construct(private EquipmentRepository $repository)
    {
    }

    public function execute(): array
    {
        return $this->repository->findAll();
    }
    
}