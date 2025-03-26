<?php

namespace App\Faction\Infrastructure\Persistence\Pdo;

use App\Faction\Domain\Faction;

class MySQLFactionToArrayTransformer
{
    public static function transform(Faction $faction): array
    {
        $data = [
            'faction_name' => $faction->getName(),
            'description' => $faction->getDescription(),
        ];

        if ($faction->getId()) {
            $data['id'] = $faction->getId();
        }

        return $data;
    }
}