<?php

namespace App\Faction\Domain;

class Faction
{

    private ?int $id = null;
    private string $faction_name;
    private string $description;

    public function __construct() 
    {
    }

    # Getters y setters
    public function setId(?int $id): self {
        $this->id = $id;
        return $this;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function setName(string $faction_name): self {
        $this->faction_name = $faction_name;
        return $this;
    }

    public function getName(): string {
        return $this->faction_name;
    }

    public function setDescription(string $description): self {
        $this->description = $description;
        return $this;
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
    public function fromArray(array $data): self {
        if (isset($data['id'])) {
            $this->setId($data['id']);
        }
        
        return $this
            ->setName($data['faction_name'])
            ->setDescription($data['description']);
    }

    /**
     * Convierte la facción a un array
     *
     * @return array
     */
    public function toArray(): array {
        $data = [
            'faction_name' => $this->faction_name,
            'description' => $this->description
        ];

        if (isset($this->id)) {
            $data['id'] = $this->id;
        }

        return $data;
    }
    
    

}