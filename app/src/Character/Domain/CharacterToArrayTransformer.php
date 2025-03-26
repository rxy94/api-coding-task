<?php

namespace App\Character\Domain;

use App\Character\Domain\Character;

class CharacterToArrayTransformer
{
    public static function transform(Character $character): array
    {
        $data = [
            # Estas claves no tienen nada que ver con el nombre de la columna de la base de datos
            'name' => $character->getName(),
            'birth-date' => $character->getBirthDate(),
            'kingdom' => $character->getKingdom(),
            'equipment-id' => $character->getEquipmentId(),
            'faction-id' => $character->getFactionId(),
        ];

        if ($character->getId()) {
            $data['id'] = $character->getId();
        }

        return $data;
        
    }
}