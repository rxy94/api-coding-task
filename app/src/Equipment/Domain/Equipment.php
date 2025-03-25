<?php

namespace App\Equipment\Domain;

use JsonSerializable;

class Equipment implements JsonSerializable
{
    public function __construct(
        private string $name,
        private string $type,
        private string $made_by,
        private ?int $id = null
    ) {
    }

    # Getters
    public function getId(): ?int {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getType(): string {
        return $this->type;
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
    public function fromArray(array $data): self 
    {
        return new self(
            $data['name'],
            $data['type'],
            $data['made_by'],
            $data['id'] ?? null
        );  
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

    /**
     *  Convierte el objeto de equipamiento a un array para serializar
     *
     * @return array
     */
    public function jsonSerialize(): array 
    {
        return $this->toArray();
    }

}