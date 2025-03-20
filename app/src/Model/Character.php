<?php

namespace App\Model;

use PDO;

class Character
{
    private int $id;
    private string $name;
    private string $birth_date;
    private string $kingdom;
    private int $equipment_id;
    private int $faction_id;

    public function __construct(private PDO $pdo)
    {
    }

    # Getters y setters
    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getBirthDate(): string
    {
        return $this->birth_date;
    }

    public function setBirthDate(string $birth_date): self
    {
        $this->birth_date = $birth_date;
        return $this;
    }

    public function getKingdom(): string
    {
        return $this->kingdom;
    }

    public function setKingdom(string $kingdom): self
    {
        $this->kingdom = $kingdom;
        return $this;
    }

    public function getEquipmentId(): int
    {
        return $this->equipment_id;
    }

    public function setEquipmentId(int $equipment_id): self
    {
        $this->equipment_id = $equipment_id;
        return $this;
    }

    public function getFactionId(): int
    {
        return $this->faction_id;
    }

    public function setFactionId(int $faction_id): self
    {
        $this->faction_id = $faction_id;
        return $this;
    }

    /**
     * Crea un nuevo personaje a partir de un array
     *
     * @param array $data
     * @param PDO $pdo
     * @return self
     */
    public function fromArray(array $data, PDO $pdo): self
    {
        $character = new self($pdo);
        
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

    /**
     * Convierte el personaje a un array
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'birth_date' => $this->birth_date,
            'kingdom' => $this->kingdom,
            'equipment_id' => $this->equipment_id,
            'faction_id' => $this->faction_id
        ];

        if (isset($this->id)) {
            $data['id'] = $this->id;
        }

        return $data;
    }

    /**
     * Busca un personaje por su ID
     *
     * @param integer $id
     * @return self|null
     */
    public function find(int $id): ?self
    {
        $stmt = $this->pdo->prepare('SELECT * FROM characters WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return self::fromArray($data, $this->pdo);
    }

    /**
     * Busca todos los personajes
     *
     * @return array
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM characters');
        $characters = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $characters[] = self::fromArray($data, $this->pdo);
        }

        return $characters;
    }

    /**
     * Guarda el personaje en la base de datos
     *
     * @return boolean
     */
    public function save(): bool
    {
        if (isset($this->id)) {
            return $this->update();
        }

        return $this->insert();
    }

    /**
     * Inserta un nuevo personaje en la base de datos
     *
     * @return boolean
     */
    private function insert(): bool
    {
        $sql = 'INSERT INTO characters (name, birth_date, kingdom, equipment_id, faction_id) 
                VALUES (:name, :birth_date, :kingdom, :equipment_id, :faction_id)';
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            'name' => $this->name,
            'birth_date' => $this->birth_date,
            'kingdom' => $this->kingdom,
            'equipment_id' => $this->equipment_id,
            'faction_id' => $this->faction_id
        ]);

        if ($result) {
            $this->id = (int) $this->pdo->lastInsertId();
        }

        return $result;
    }

    /**
     * Actualiza un personaje en la base de datos
     *
     * @return boolean
     */
    private function update(): bool
    {
        $sql = 'UPDATE characters 
                SET name = :name, 
                    birth_date = :birth_date, 
                    kingdom = :kingdom, 
                    equipment_id = :equipment_id, 
                    faction_id = :faction_id 
                WHERE id = :id';
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $this->id,
            'name' => $this->name,
            'birth_date' => $this->birth_date,
            'kingdom' => $this->kingdom,
            'equipment_id' => $this->equipment_id,
            'faction_id' => $this->faction_id
        ]);
    }

    /**
     * Elimina un personaje de la base de datos
     *
     * @return boolean
     */
    public function delete(): bool
    {
        if (!isset($this->id)) {
            return false;
        }

        $stmt = $this->pdo->prepare('DELETE FROM characters WHERE id = :id');
        return $stmt->execute(['id' => $this->id]);
    }

}
