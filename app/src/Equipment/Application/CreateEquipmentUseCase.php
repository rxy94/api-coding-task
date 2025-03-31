<?php

namespace App\Equipment\Application;

use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\EquipmentFactory;
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
    
    public function execute(
        CreateEquipmentUseCaseRequest $request
    ): CreateEquipmentUseCaseResponse {

        # Validamos los datos
        $this->equipmentValidator->validate(
            $request->getName(), 
            $request->getType(), 
            $request->getMadeBy()
        );

        $equipment = EquipmentFactory::build(
            $request->getName(),
            $request->getType(),
            $request->getMadeBy()
        );

        # Guardamos el equipamiento
        $this->equipmentRepository->save($equipment);

        return new CreateEquipmentUseCaseResponse($equipment);
        
    }

}
