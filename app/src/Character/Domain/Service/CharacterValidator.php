<?php

namespace App\Character\Domain\Service;

use App\Character\Domain\Exception\CharacterValidationException;

class CharacterValidator
{
    public function validate(
        string $name,
        string $birthDate,
        string $kingdom,
        int $equipmentId,
        int $factionId
    ): void {

        # Validamos los campos requeridos
        if (empty($name)) {
            throw CharacterValidationException::withNameError();
        } elseif (strlen($name) > 100) {
            throw CharacterValidationException::withNameLengthError();
        }

        if (empty($birthDate)) {
            throw CharacterValidationException::withBirthDateError();
        } else {
            $date = \DateTime::createFromFormat('Y-m-d', $birthDate);
            if (!$date || $date->format('Y-m-d') !== $birthDate) {
                throw CharacterValidationException::withBirthDateFormatError();
            }
        }

        if (empty($kingdom)) {
            throw CharacterValidationException::withKingdomError();
        } elseif (strlen($kingdom) > 100) {
            throw CharacterValidationException::withKingdomLengthError();
        }

        if ($equipmentId <= 0) {
            throw CharacterValidationException::withEquipmentIdError();
        } elseif (!is_int($equipmentId)) {
            throw CharacterValidationException::withEquipmentIdTypeError();
        }

        if ($factionId <= 0) {
            throw CharacterValidationException::withFactionIdError();
        } elseif (!is_int($factionId)) {
            throw CharacterValidationException::withFactionIdTypeError();
        }

    }
} 