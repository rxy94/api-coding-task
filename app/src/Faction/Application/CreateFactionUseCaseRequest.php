<?php

namespace App\Faction\Application;

class CreateFactionUseCaseRequest
{
    public function __construct(
        private string $faction_name,
        private string $description,
        private ?int $id = null
    ) {
    }

    public function getFactionName(): string
    {
        return $this->faction_name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}