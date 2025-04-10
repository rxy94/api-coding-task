<?php

namespace App\Character\Infrastructure\Persistence\Pdo;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterFactory;

/**
 * Factory para crear personajes a partir de un array
 */
class MySQLCharacterFactory
{
    public static function buildFromArray(array $data): Character
    {
        return CharacterFactory::build(
            $data['name'],
            $data['birth_date'],
            $data['kingdom'],
            $data['equipment_id'],
            $data['faction_id'],
            $data['id']
        );
    }
}