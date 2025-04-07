<?php

namespace App\Equipment\Application;

use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Infrastructure\Persistence\Pdo\Exception\EquipmentNotFoundException;

class UpdateEquipmentUseCase
{
    public function __construct(
        private EquipmentRepository $repository,
    ) {
    }

    public function execute(
        UpdateEquipmentUseCaseRequest $request
    ): UpdateEquipmentUseCaseResponse
    {
        $oldEquipment = $this->repository->findById($request->getId());

        if (!$oldEquipment) {
            throw EquipmentNotFoundException::build();
        }

        $updatedEquipment = new Equipment(
            name: $request->getName(),
            type: $request->getType(),
            made_by: $request->getMadeBy(),
            id: $oldEquipment->getId()
        );

        $savedEquipment = $this->repository->save($updatedEquipment);

        return new UpdateEquipmentUseCaseResponse($savedEquipment);
    }
} 