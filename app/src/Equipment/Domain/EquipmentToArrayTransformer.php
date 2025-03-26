<?php

namespace App\Equipment\Domain;

use App\Equipment\Domain\Equipment;

class EquipmentToArrayTransformer
{
    public static function transform(Equipment $equipment): array
    {
        $data = [
            'name'   => $equipment->getName(),
            'type'   => $equipment->getType(),
            'made-by' => $equipment->getMadeBy(),
        ];

        if ($equipment->getId()) {
            $data['id'] = $equipment->getId();
        }

        return $data;
    }
}