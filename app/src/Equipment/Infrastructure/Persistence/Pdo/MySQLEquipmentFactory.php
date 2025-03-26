<?php

namespace App\Equipment\Infrastructure\Persistence\Pdo;

use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\EquipmentFactory;

class MySQLEquipmentFactory
{
    public static function buildFromArray(array $data): Equipment
    {
        return EquipmentFactory::build(
            $data['name'],
            $data['type'],
            $data['made_by'],
            $data['id']
        );
    }
}