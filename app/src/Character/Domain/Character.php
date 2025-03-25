<?php

namespace App\Character\Domain;

class Character
{
    public function __construct(
        private string $name,
        private string $birth_date,
        private string $kingdom,
        private int $equipment_id,
        private int $faction_id,
        private ?int $id = null
    ) {
    }

    # Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBirthDate(): string
    {
        return $this->birth_date;
    }

    public function getKingdom(): string
    {
        return $this->kingdom;
    }

    public function getEquipmentId(): int
    {
        return $this->equipment_id;
    }

    public function getFactionId(): int
    {
        return $this->faction_id;
    } 

}