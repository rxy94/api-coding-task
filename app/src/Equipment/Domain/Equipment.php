<?php

namespace App\Equipment\Domain;

use App\Equipment\Domain\Exception\EquipmentValidationException;

class Equipment
{
    public function __construct(
        private string $name,
        private string $type,
        private string $made_by,
        private ?int $id = null
    ) {
        $this->validateName();
        $this->validateNameLength();
        $this->validateType();
        $this->validateTypeLength();
        $this->validateMadeBy();
        $this->validateMadeByLength();
        $this->validateId();
    }

    # Validaciones
    private function validateName(): void
    {
        if (empty($this->name)) {
            throw EquipmentValidationException::withNameError();
        }
    }

    private function validateNameLength(): void
    {
        if (strlen($this->name) > 100) {
            throw EquipmentValidationException::withNameLengthError();
        }
    }

    private function validateType(): void
    {
        if (empty($this->type)) {
            throw EquipmentValidationException::withTypeError();
        }
    }

    private function validateTypeLength(): void
    {
        if (strlen($this->type) > 100) {
            throw EquipmentValidationException::withTypeErrorLengthError();
        }
    }

    private function validateMadeBy(): void
    {
        if (empty($this->made_by)) {
            throw EquipmentValidationException::withMadeByError();
        }
    }

    private function validateMadeByLength(): void
    {
        if (strlen($this->made_by) > 100) {
            throw EquipmentValidationException::withMadeByLengthError();
        }
    }

    private function validateId(): void
    {
        if ($this->id < 0) {
            throw EquipmentValidationException::withIdNonPositive();
        }
    }

    # Getters
    public function getId(): ?int {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getType(): string {
        return $this->type;
    }

    public function getMadeBy(): string {
        return $this->made_by;
    }
}