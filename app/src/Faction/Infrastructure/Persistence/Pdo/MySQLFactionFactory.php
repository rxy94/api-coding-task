<?php

namespace App\Faction\Infrastructure\Persistence\Pdo;

use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionFactory;

class MySQLFactionFactory
{
    public static function buildFromArray(array $data): Faction
    {
        return FactionFactory::build(
            $data['faction_name'],
            $data['description'],
            $data['id'] ?? null
        );
    }   
}