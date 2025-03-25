<?php

namespace App\Faction\Domain;

use JsonSerializable;

class Faction implements JsonSerializable
{
    public function __construct(
        private string $faction_name,
        private string $description,
        private ?int $id = null
    ) {
    }

    # Getters
    public function getId(): ?int {
        return $this->id;
    }

    public function getName(): string {
        return $this->faction_name;
    }

    public function getDescription(): string {
        return $this->description;
    }

    /**
     * Crea una facción a partir de un array
     *
     * @param array $data
     * @return self
     */
    public function fromArray(array $data): self 
    {
        return new self(
            $data['faction_name'],
            $data['description'],
            $data['id'] ?? null
        );
    }

    /**
     * Convierte la facción a un array
     *
     * @return array
     */
    public function toArray(): array {
        $data = [
            'faction_name' => $this->faction_name,
            'description'  => $this->description
        ];

        if ($this->id !== null) {
            $data['id'] = $this->id;
        }

        return $data;
    }
    
    /**
     * Convierte la facción a un array para serializar
     *
     * @return array
     */
    public function jsonSerialize(): array {
        return $this->toArray();
    }

}