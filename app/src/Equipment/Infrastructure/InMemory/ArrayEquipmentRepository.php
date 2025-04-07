<?php

namespace App\Equipment\Infrastructure\InMemory;

use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\EquipmentFactory;
use App\Equipment\Domain\EquipmentRepository;

class ArrayEquipmentRepository implements EquipmentRepository
{
    public function __construct(
        private array $equipments = []
    ) {
    }

    public function findById(int $id): ?Equipment
    {
        if (!isset($this->equipments[$id])) {
            //throw EquipmentNotFoundException::withId($id);
            return null;
        }

        return $this->equipments[$id];
    }

    public function save(Equipment $equipment): Equipment
    {
        if ($equipment->getId() !== null) {
            $this->equipments[$equipment->getId()] = $equipment;
            
            return $equipment;
        }

        $equipment = EquipmentFactory::build(
            $equipment->getName(),
            $equipment->getType(),
            $equipment->getMadeBy(),
            count($this->equipments) + 1
        );

        $this->equipments[$equipment->getId()] = $equipment;

        return $equipment;
    }

    public function findAll(): array
    {
        return $this->equipments;
    }

    public function delete(Equipment $equipment): bool
    {
        unset($this->equipments[$equipment->getId()]);

        return true;
    }
} 