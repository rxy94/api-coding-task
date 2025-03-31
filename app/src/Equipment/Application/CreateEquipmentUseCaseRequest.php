<?php

namespace App\Equipment\Application;

class CreateEquipmentUseCaseRequest
{
    public function __construct(
        private readonly string $name,
        private readonly string $type,
        private readonly string $made_by,
    ) {
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
        return $this->made_by;
    }
    
}