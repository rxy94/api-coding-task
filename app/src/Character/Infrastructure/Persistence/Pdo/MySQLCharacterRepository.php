<?php

namespace App\Character\Infrastructure\Persistence\Pdo;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Infrastructure\Persistence\Pdo\Exception\CharacterNotFoundException;
use App\Character\Infrastructure\Persistence\Pdo\Exception\CharactersNotFoundException;
use App\Shared\Infrastructure\Persistence\Pdo\Exception\RowDeletionFailedException;
use App\Shared\Infrastructure\Persistence\Pdo\Exception\RowInsertionFailedException;
use App\Shared\Infrastructure\Persistence\Pdo\Exception\RowUpdateFailedException;
use PDO;

/**
 * Maneja las operaciones directas con la base de datos
 */
class MySQLCharacterRepository implements CharacterRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findById(int $id): ?Character
    {
        $sql = 'SELECT * FROM characters WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            throw CharacterNotFoundException::build();
        }

        return MySQLCharacterFactory::buildFromArray($data);
    }

    public function findAll(): array
    {
        try {
            $sql = 'SELECT * FROM characters';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $characters = [];

            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $characters[] = MySQLCharacterFactory::buildFromArray($data);
            }

            return $characters;

        } catch (\PDOException $e) {
            throw CharactersNotFoundException::build();

        }
    }

    public function save(Character $character): Character
    {
        if ($character->getId() === null) {
            return $this->insert($character);
        }

        return $this->update($character);
    }

    private function insert(Character $character): Character
    {
        $sql = 'INSERT INTO characters (name, birth_date, kingdom, equipment_id, faction_id) 
                VALUES (:name, :birth_date, :kingdom, :equipment_id, :faction_id)';
        
        $stmt = $this->pdo->prepare($sql);

        $result = $stmt->execute(
            MySQLCharacterToArrayTransformer::transform($character)
        );

        if (!$result) {
            throw RowInsertionFailedException::build();
        }

        return MySQLCharacterFactory::buildFromArray([
            'id'           => $this->pdo->lastInsertId(),
            'name'         => $character->getName(),
            'birth_date'   => $character->getBirthDate(),
            'kingdom'      => $character->getKingdom(),
            'equipment_id' => $character->getEquipmentId(),
            'faction_id'   => $character->getFactionId(),
        ]);
    }

    private function update(Character $character): Character
    {
        $sql = 'UPDATE characters 
                SET name = :name, 
                    birth_date = :birth_date, 
                    kingdom = :kingdom, 
                    equipment_id = :equipment_id, 
                    faction_id = :faction_id 
                WHERE id = :id';
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            'name'         => $character->getName(),
            'birth_date'   => $character->getBirthDate(),
            'kingdom'      => $character->getKingdom(),
            'equipment_id' => $character->getEquipmentId(),
            'faction_id'   => $character->getFactionId(),
            'id'           => $character->getId(),
        ]);

        if (!$result) {
            throw RowUpdateFailedException::build();
        }

        return $character;
    }

    public function delete(Character $character): bool
    {
        if (null === $character->getId()) {
            throw CharacterNotFoundException::build();
        }

        $sql = 'DELETE FROM characters WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute(['id' => $character->getId()]);

        if (!$result) {
            throw RowDeletionFailedException::build();
        }

        return $result;

    }

}