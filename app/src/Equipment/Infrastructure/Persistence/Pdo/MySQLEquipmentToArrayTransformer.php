<?php

namespace App\Equipment\Infrastructure\Persistence\Pdo;

use App\Equipment\Domain\Equipment;

class MySQLEquipmentToArrayTransformer
{
    public static function transform(Equipment $equipment): array
    {
        $data = [
            'id'     => $equipment->getId(),
            'name'   => $equipment->getName(),
            'type'   => $equipment->getType(),
            'made_by' => $equipment->getMadeBy(),
        ];

        if ($equipment->getId() !== null) {
            $data['id'] = $equipment->getId();
        }

        return $data;
    }
}