<?php

namespace App\Faction\Application;

class UpdateFactionUseCaseRequest
{
    public function __construct(
        private readonly int $id,
        private readonly string $faction_name,
        private readonly string $description,
    ) { 
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->faction_name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

} 
 