<?php

namespace App\Character\Application;

class UpdateCharacterUseCaseRequest
{
    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly string $birthDate,
        private readonly string $kingdom,
        private readonly int $equipmentId,
        private readonly int $factionId,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBirthDate(): string
    {
        return $this->birthDate;
    }

    public function getKingdom(): string
    {
        return $this->kingdom;
    }

    public function getEquipmentId(): int
    {
        return $this->equipmentId;
    }

    public function getFactionId(): int
    {
        return $this->factionId;
    }
} 