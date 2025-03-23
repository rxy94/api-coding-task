<?php

namespace App\Equipment\Application;

use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\EquipmentRepository;

class CreateEquipmentUseCase
{
    public function __construct(private EquipmentRepository $equipmentRepository)
    {
    }

    /**
     * 
     * @param string $name
     * @param string $type
     * @param string $made_by
     * @return Equipment
     */     
    public function execute(string $name, string $type, string $made_by): Equipment
    {
        //TODO: Validar datos

        $equipment = new Equipment();
        $equipment->setName($name);
        $equipment->setType($type);
        $equipment->setMadeBy($made_by);

        $this->equipmentRepository->save($equipment);

        return $equipment;
    }

}
