<?php

namespace App\Faction\Domain;

use App\Faction\Domain\Exception\FactionValidationException;

class Faction
{
    public function __construct(
        private string $faction_name,
        private string $description,
        private ?int $id = null
    ) {
        $this->validateFactionName();
        $this->validateFactionNameLength();
        $this->validateDescription();
        $this->validateDescriptionLength();
        $this->validateId();
    }

    # Validaciones
    private function validateFactionName(): void
    {
        if (empty($this->faction_name)) {
            throw FactionValidationException::withFactionNameError();
        }
    }

    private function validateFactionNameLength(): void
    {
        if (strlen($this->faction_name) > 100) {
            throw FactionValidationException::withFactionNameLengthError();
        }
    }

    private function validateDescription(): void
    {
        if (empty($this->description)) {
            throw FactionValidationException::withDescriptionError();
        }
    }

    private function validateDescriptionLength(): void
    {
        if (strlen($this->description) > 1000) {
            throw FactionValidationException::withDescriptionLengthError();
        }
    }

    private function validateId(): void
    {
        if ($this->id < 0) {
            throw FactionValidationException::withIdNonPositive();
        }
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