<?php

namespace App\Faction\Domain;

interface FactionRepository 
{
    public function find(int $id): ?Faction;

    public function findAll(): array;

    public function save(Faction $faction): Faction;

    public function delete(Faction $faction): bool;
    
}