<?php

namespace App\Equipment\Application;

class UpdateEquipmentUseCaseRequest
{
    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly string $type,
        private readonly string $madeBy,
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

    public function getType(): string
    {
        return $this->type;
    }

    public function getMadeBy(): string
    {
        return $this->madeBy;
    }
} 
 