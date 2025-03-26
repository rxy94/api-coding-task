<?php

namespace App\Character\Infrastructure\Http;

use App\Character\Domain\Character;

class CharacterToJsonTransformer
{
    public static function transform(Character $character): string
    {
        return json_encode([
            'id'           => $character->getId(),
            'name'         => $character->getName(),
            'birth_date'   => $character->getBirthDate(),
            'kingdom'      => $character->getKingdom(),
            'equipment_id' => $character->getEquipmentId(),
            'faction_id'   => $character->getFactionId(),
        ]);
    }

    /* public static function transform(Character $character): array
    {
        return [
            'id'           => $character->getId(),
            'name'         => $character->getName(),
            'birth_date'   => $character->getBirthDate(),
            'kingdom'      => $character->getKingdom(),
            'equipment_id' => $character->getEquipmentId(),
            'faction_id'   => $character->getFactionId(),
        ];
    } */
}