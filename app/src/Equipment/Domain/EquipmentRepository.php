<?php

namespace App\Equipment\Domain;

interface EquipmentRepository
{   
    public function findById(int $id): ?Equipment;
    
    public function findAll(): array;   
    
    public function save(Equipment $equipment): Equipment;

    public function delete(Equipment $equipment): bool;

}