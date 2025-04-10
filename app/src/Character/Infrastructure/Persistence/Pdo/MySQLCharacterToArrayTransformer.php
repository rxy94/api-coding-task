<?php

namespace App\Character\Infrastructure\Persistence\Pdo;

use App\Character\Domain\Character;

/**
 * Transforma un personaje en un array para ser insertado en la base de datos
 */
class MySQLCharacterToArrayTransformer
{
    public static function transform(Character $character): array
    {
        $data = [
            'name' => $character->getName(),
            'birth_date' => $character->getBirthDate(),
            'kingdom' => $character->getKingdom(),
            'equipment_id' => $character->getEquipmentId(),
            'faction_id' => $character->getFactionId(),
        ];

        if ($character->getId()) {
            $data['id'] = $character->getId();
        }

        return $data;
        
    }
}