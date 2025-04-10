<?php

namespace App\Equipment\Application;

use App\Equipment\Domain\EquipmentFactory;
use App\Equipment\Domain\EquipmentRepository;

class CreateEquipmentUseCase
{
    public function __construct(
        private EquipmentRepository $equipmentRepository
    ) {
    }
    
    public function execute(
        CreateEquipmentUseCaseRequest $request
    ): CreateEquipmentUseCaseResponse 
    {
        $equipment = EquipmentFactory::build(
            $request->getName(),
            $request->getType(),
            $request->getMadeBy(),
            $request->getId()
        );

        # Guardamos el equipamiento
        $equipment = $this->equipmentRepository->save($equipment);

        return new CreateEquipmentUseCaseResponse($equipment);
        
    }

}
