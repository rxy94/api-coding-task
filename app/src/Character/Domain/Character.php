<?php

namespace App\Character\Domain;

class Character
{
    private int $id;
    private string $name;
    private string $birth_date;
    private string $kingdom;
    private int $equipment_id;
    private int $faction_id;

    public function __construct()
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
     * @return self
     */
    public function fromArray(array $data): self
    {
        $character = new self();
        
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

}