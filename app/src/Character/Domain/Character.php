<?php

namespace App\Character\Domain;
use JsonSerializable;

class Character implements JsonSerializable
{
    public function __construct(
        private string $name,
        private string $birth_date,
        private string $kingdom,
        private int $equipment_id,
        private int $faction_id,
        private ?int $id = null
    ) {
    }

    # Getters y setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBirthDate(): string
    {
        return $this->birth_date;
    }

    public function getKingdom(): string
    {
        return $this->kingdom;
    }

    public function getEquipmentId(): int
    {
        return $this->equipment_id;
    }

    public function getFactionId(): int
    {
        return $this->faction_id;
    }

    /**
     * Crea un nuevo personaje a partir de un array
     *
     * @param array $data
     * @return self
     */
    public function fromArray(array $data): self
    {
        return new self(
            $data['name'],
            $data['birth_date'],
            $data['kingdom'],
            $data['equipment_id'],
            $data['faction_id'],
            $data['id'] ?? null
        );
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

        if ($this->id !== null) {
            $data['id'] = $this->id;
        }

        return $data;
    }

    /**
     * Convierte el personaje a un array para serializar
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }  

}