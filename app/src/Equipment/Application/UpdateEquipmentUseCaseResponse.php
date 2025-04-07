<?php

namespace App\Equipment\Application;

use App\Equipment\Domain\Equipment;

class UpdateEquipmentUseCaseResponse
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