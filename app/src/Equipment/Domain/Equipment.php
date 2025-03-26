<?php

namespace App\Equipment\Domain;

class Equipment
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

}