<?php

namespace App\Character\Domain;

use App\Character\Domain\Exception\CharacterValidationException;

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

        $this->validateName();
        $this->validateNameLength();
        $this->validateBirthDate();
        $this->validateBirthDateFormat();
        $this->validateKingdom();
        $this->validateKingdomLength();
        $this->validateEquipmentIdRequired();
        $this->validateEquipmentIdNonPositive();
        $this->validateFactionIdRequired();
        $this->validateFactionIdNonPositive();
        $this->validateId();

    }

    # Validaciones
    private function validateName(): void
    {
        if (empty($this->name)) {
            throw CharacterValidationException::withNameRequired();
        }
    }

    private function validateNameLength(): void
    {
        if (strlen($this->name) > 100) {
            throw CharacterValidationException::withNameLengthError();
        }
    }

    private function validateBirthDate(): void
    {
        if (empty($this->birth_date)) {
            throw CharacterValidationException::withBirthDateRequired();
        }
    }

    private function validateBirthDateFormat(): void
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->birth_date)) {
            throw CharacterValidationException::withBirthDateFormatError();
        }
    }

    private function validateKingdom(): void
    {
        if (empty($this->kingdom)) {
            throw CharacterValidationException::withKingdomRequired();
        }
    }

    private function validateKingdomLength(): void
    {
        if (strlen($this->kingdom) > 100) {
            throw CharacterValidationException::withKingdomLengthError();
        }
    }

    private function validateEquipmentIdRequired(): void
    {
        if (empty($this->equipment_id)) {
            throw CharacterValidationException::withEquipmentIdRequired();
        }
    }

    private function validateEquipmentIdNonPositive(): void
    {
        if ($this->equipment_id <= 0) {
            throw CharacterValidationException::withEquipmentIdNonPositive();
        }
    }

    private function validateFactionIdRequired(): void
    {
        if (empty($this->faction_id)) {
            throw CharacterValidationException::withFactionIdRequired();
        }
    }

    private function validateFactionIdNonPositive(): void
    {
        if ($this->faction_id <= 0) {
            throw CharacterValidationException::withFactionIdNonPositive();
        }
    }

    private function validateId(): void
    {
        if ($this->id < 0) {
            throw CharacterValidationException::withIdNonPositive();
        }
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