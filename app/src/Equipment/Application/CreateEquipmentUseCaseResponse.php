<?php

namespace App\Equipment\Application;

use App\Equipment\Domain\Equipment;

class CreateEquipmentUseCaseResponse
{
    public function __construct(
        private readonly Equipment $equipment,
    ) {
    }

    public function getEquipment(): Equipment
    {
        return $this->equipment;
    }
}