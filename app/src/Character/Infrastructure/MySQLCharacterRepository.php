<?php

namespace App\Character\Infrastructure;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use PDO;

class MySQLCharacterRepository implements CharacterRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function find(int $id): ?Character
    {
        $stmt = $this->pdo->prepare('SELECT * FROM characters WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->fromArray($data);
    }

    private function fromArray(array $data): Character
    {
        $character = new Character();
        
        if (isset($data['id'])) {
            $character->setId($data['id']);
        }
        
        return $character
            ->setName($data['name'])
            ->setBirthDate($data['birth_date'])
            ->setKingdom($data['kingdom'])
            ->setEquipmentId($data['equipment_id'])
            ->setFactionId($data['faction_id']);
            
    }

    public function findAll(): array
    {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM characters');
            $stmt->execute();
            $characters = [];

            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $character = $this->fromArray($data);
                $characters[] = $character;
            }

            //var_dump($characters);

            return $characters;

        } catch (\PDOException $e) {
            throw new \PDOException("Error al obtener los personajes: " . $e->getMessage());

        }
    }

    public function save(Character $character): Character
    {
        if (isset($this->id)) {
            return $this->update($character);
        }

        return $this->insert($character);
    }

    private function insert(Character $character): Character
    {
        $sql = 'INSERT INTO characters (name, birth_date, kingdom, equipment_id, faction_id) 
                VALUES (:name, :birth_date, :kingdom, :equipment_id, :faction_id)';
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            'name' =>           $character->getName(),
            'birth_date' =>     $character->getBirthDate(),
            'kingdom' =>        $character->getKingdom(),
            'equipment_id' =>   $character->getEquipmentId(),
            'faction_id' =>     $character->getFactionId(),
        ]);

        if ($result) {
            $character->setId((int) $this->pdo->lastInsertId());
        }

        return $character;
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
        $stmt->execute([
            'id' =>             $character->getId(),
            'name' =>           $character->getName(),
            'birth_date' =>     $character->getBirthDate(),
            'kingdom' =>        $character->getKingdom(),
            'equipment_id' =>   $character->getEquipmentId(),
            'faction_id' =>     $character->getFactionId(),
        ]);

        return $character;
    }

    public function delete(Character $character): bool
    {
        if (null !== $character->getId()) {
            return false;
        }

        $stmt = $this->pdo->prepare('DELETE FROM characters WHERE id = :id');
        return $stmt->execute(['id' => $character->getId()]);
    }

}