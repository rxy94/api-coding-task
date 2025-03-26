<?php

namespace App\Faction\Domain;

use App\Faction\Domain\Faction;

class FactionToArrayTransformer
{
    public static function transform(Faction $faction): array
    {
        $data = [
            'faction-name' => $faction->getName(),
            'description' => $faction->getDescription(),
        ];

        if ($faction->getId()) {    
            $data['id'] = $faction->getId();
        }

        return $data;   
    }
}   