<?php

namespace App\Faction\Infrastructure\Persistence\Pdo;

use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionRepository;
use App\Faction\Infrastructure\Persistence\Pdo\Exception\FactionNotFoundException;
use App\Faction\Infrastructure\Persistence\Pdo\Exception\FactionsNotFoundException;
use App\Faction\Infrastructure\Persistence\Pdo\MySQLFactionFactory;
use App\Shared\Infrastructure\Persistence\Pdo\Exception\RowDeletionFailedException;
use App\Shared\Infrastructure\Persistence\Pdo\Exception\RowInsertionFailedException;
use App\Shared\Infrastructure\Persistence\Pdo\Exception\RowUpdateFailedException;
use PDO;

class MySQLFactionRepository implements FactionRepository 
{
    public function __construct(private PDO $pdo) 
    {
    }

    public function findById(int $id): ?Faction 
    {
        $sql = 'SELECT * FROM factions WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            throw FactionNotFoundException::build();
        }

        return MySQLFactionFactory::buildFromArray($data);
        
    }

    public function findAll(): array 
    {
        try {
            $sql = 'SELECT * FROM factions';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $factions = [];

            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $factions[] = MySQLFactionFactory::buildFromArray($data);
            }

            return $factions;

        } catch (\PDOException $e) {
            throw FactionsNotFoundException::build();
        }
    }

    public function save(Faction $faction): Faction 
    {
        if ($faction->getId() === null) {
            return $this->insert($faction);
        }

        return $this->update($faction);
    }

    private function insert(Faction $faction): Faction 
    {
        $sql = 'INSERT INTO factions (faction_name, description)    
                VALUES (:faction_name, :description)';

        $stmt = $this->pdo->prepare($sql);

        $result = $stmt->execute(
            MySQLFactionToArrayTransformer::transform($faction)
        );

        if (!$result) {
            throw RowInsertionFailedException::build();
        }

        return MySQLFactionFactory::buildFromArray(
            [
                'id' => $this->pdo->lastInsertId(),
                'faction_name' => $faction->getName(),
                'description' => $faction->getDescription()
            ]
        );
    }

    private function update(Faction $faction): Faction 
    {
        $sql = 'UPDATE factions 
                SET faction_name = :faction_name, 
                    description = :description 
                WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute(
            MySQLFactionToArrayTransformer::transform($faction)
        );

        if (!$result) {
            throw RowUpdateFailedException::build();
        }

        return $faction;
    }

    public function delete(Faction $faction): bool 
    {
        if (null === $faction->getId()) {
            throw FactionNotFoundException::build();
        }
 
        $sql = 'DELETE FROM factions WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute(['id' => $faction->getId()]);

        if (!$result) {
            throw RowDeletionFailedException::build();
        }

        return $result;
        
    }

}
