<?php

namespace App\Faction\Infrastructure\Persistence\InMemory;

use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionFactory;
use App\Faction\Domain\FactionRepository;

class ArrayFactionRepository implements FactionRepository
{
    public function __construct(
        private array $factions = []
    ) {
    }

    public function save(Faction $faction): Faction
    {
        if ($faction->getId() !== null) {
            $this->factions[$faction->getId()] = $faction;
            return $faction;
        }

        $faction = FactionFactory::build(
            $faction->getName(),
            $faction->getDescription(),
            count($this->factions) + 1
        );

        $this->factions[$faction->getId()] = $faction;

        return $faction;
    }

    public function findById(int $id): ?Faction
    {
        if (!isset($this->factions[$id])) {
            return null;
        }

        return $this->factions[$id];
    }

    public function findAll(): array
    {
        return $this->factions;
    }
    
    public function delete(Faction $faction): bool
    {
        unset($this->factions[$faction->getId()]);
        return true;
    }
    
}