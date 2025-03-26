<?php

namespace App\Faction\Domain;

class Faction
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

}