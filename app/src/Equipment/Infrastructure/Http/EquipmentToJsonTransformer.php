<?php

namespace App\Equipment\Infrastructure\Http;

use App\Equipment\Domain\Equipment;

class EquipmentToJsonTransformer
{
    public static function transform(Equipment $equipment): string
    {
        return json_encode([
            'id' => $equipment->getId(),
            'name' => $equipment->getName(),
            'type' => $equipment->getType(),
            'made_by' => $equipment->getMadeBy(),
        ]);
    }
}