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

    public function findById(int $id): ?Faction 
    {
        $sql = 'SELECT * FROM factions WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->fromArray($data);
        
    }

    private function fromArray(array $data): Faction 
    {
        $faction = new Faction(
            $data['faction_name'],
            $data['description'],
            $data['id'] ?? null
        );
        
        return $faction;
    }

    public function findAll(): array 
    {
        try {
            $sql = 'SELECT * FROM factions';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $factions = [];

            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $faction = self::fromArray($data);
                $factions[] = $faction;
            }

            return $factions;

        } catch (\PDOException $e) {
            throw new \PDOException("Error al obtener las facciones: " . $e->getMessage());
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
        $result = $stmt->execute([
            'faction_name' => $faction->getName(),
            'description'  => $faction->getDescription(),
        ]);
        
        if ($result) {
            $id = (int) $this->pdo->lastInsertId();
            return new Faction(
                $faction->getName(),
                $faction->getDescription(),
                $id
            );
        }

        throw new \RuntimeException('Error al insertar la facción');
    }

    private function update(Faction $faction): Faction 
    {
        $sql = 'UPDATE factions 
                SET faction_name = :faction_name, 
                    description = :description 
                WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            'id'           => $faction->getId(),
            'faction_name' => $faction->getName(),
            'description'  => $faction->getDescription(),
        ]);

        if ($result) {
            $id = (int) $this->pdo->lastInsertId();
            return new Faction(
                $faction->getName(),
                $faction->getDescription(),
                $id
            );
        }

        throw new \RuntimeException('Error al actualizar la facción');
    }

    public function delete(Faction $faction): bool 
    {
        if (null !== $faction->getId()) {
            return false;
        }

        $sql = 'DELETE FROM factions WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $faction->getId()]);
    }

}
