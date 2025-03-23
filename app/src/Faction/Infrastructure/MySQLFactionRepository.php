<?php

namespace App\Faction\Infrastructure;

use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionRepository;
use PDO;

class MySQLFactionRepository implements FactionRepository 
{
    public function __construct(private PDO $pdo) 
    {
    }

    public function find(int $id): ?Faction 
    {
        $stmt = $this->pdo->prepare('SELECT * FROM factions WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return self::fromArray($data, $this->pdo);
        
    }

    private function fromArray(array $data): Faction 
    {
        $faction = new Faction();

        if (isset($data['id'])) {
            $faction->setId($data['id']);
        }
        
        return $faction
            ->setName($data['faction_name'])
            ->setDescription($data['description']);
    }

    public function findAll(): array 
    {
        $stmt = $this->pdo->query('SELECT * FROM factions');
        $factions = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $factions[] = self::fromArray($data);
        }

        return $factions;
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
        $result = $stmt->execute([
            'faction_name' => $faction->getName(),
            'description' => $faction->getDescription(),
        ]);
        
        if ($result) {
            $faction->setId((int) $this->pdo->lastInsertId());
        }

        return $faction;
    }

    private function update(Faction $faction): Faction 
    {
        $sql = 'UPDATE factions 
                SET faction_name = :faction_name, 
                    description = :description 
                WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            'id' => $faction->getId(),
            'faction_name' => $faction->getName(),
            'description' => $faction->getDescription(),
        ]);

        if ($result) {
            $faction->setId((int) $this->pdo->lastInsertId());
        }

        return $faction;
    }

    public function delete(Faction $faction): bool 
    {
        if (null !== $faction->getId()) {
            return false;
        }

        $stmt = $this->pdo->prepare('DELETE FROM factions WHERE id = :id');
        return $stmt->execute(['id' => $faction->getId()]);
    }

}
