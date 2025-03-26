<?php

namespace App\Faction\Domain;

class FactionFactory
{
    public static function build(
        string $faction_name,
        string $description,
        ?int $id = null
    ): Faction {
        return new Faction(
            $faction_name, 
            $description, 
            $id
        );
    }
}