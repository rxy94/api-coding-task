<?php

namespace App\Equipment\Application;

use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Domain\Service\EquipmentValidator;
class CreateEquipmentUseCase
{
    public function __construct(
        private EquipmentRepository $equipmentRepository,
        private EquipmentValidator $equipmentValidator
    )
    {
    }

    /**
     * 
     * @param string $name
     * @param string $type
     * @param string $made_by
     * @return Equipment
     */     
    public function execute(
        string $name, 
        string $type, 
        string $made_by
    ): Equipment {

        # Validamos los datos
        $this->equipmentValidator->validate($name, $type, $made_by);

        $equipment = new Equipment(
            $name,
            $type,
            $made_by
        );

        # Guardamos el equipamiento
        return $this->equipmentRepository->save($equipment);
    }

}
