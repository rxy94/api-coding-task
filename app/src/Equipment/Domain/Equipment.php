<?php

namespace App\Equipment\Domain;

class Equipment
{
    private ?int $id = null;
    private string $name;
    private string $type;
    private string $made_by;

    public function __construct() 
    {
    }

     # Getters and Setters
     public function setId(?int $id): self {
        $this->id = $id;
        return $this;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function setName(string $name): self {
        $this->name = $name;
        return $this;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setType(string $type): self {
        $this->type = $type;
        return $this;
    }

    public function getType(): string {
        return $this->type;
    }

    public function setMadeBy(string $made_by): self {
        $this->made_by = $made_by;
        return $this;
    }

    public function getMadeBy(): string {
        return $this->made_by;
    }

    /**
     *  Convierte un array en un objeto de equipamiento
     *
     * @param array $data
     * @return self
     */
    public function fromArray(array $data): self {
        if (isset($data['id'])) {
            $this->setId($data['id']);
        }
        
        return $this
            ->setName($data['name'])
            ->setType($data['type'])
            ->setMadeBy($data['made_by']);
    }

    /**
     *  Convierte un objeto de equipamiento en un array
     *
     * @return array
     */
    public function toArray(): array {
        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'made_by' => $this->made_by
        ];

        if (isset($this->id)) {
            $data['id'] = $this->id;
        }

        return $data;
    }
    
}