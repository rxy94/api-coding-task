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
        $builder = CharacterValidationException::builder();

        # Validamos los campos requeridos
        if (empty($name)) {
            $builder->withNameError();
        } elseif (strlen($name) > 100) {
            $builder->withNameLengthError();
        }

        if (empty($birthDate)) {
            $builder->withBirthDateError();
        } else {
            $date = \DateTime::createFromFormat('Y-m-d', $birthDate);
            if (!$date || $date->format('Y-m-d') !== $birthDate) {
                $builder->withBirthDateFormatError();
            }
        }

        if (empty($kingdom)) {
            $builder->withKingdomError();
        } elseif (strlen($kingdom) > 100) {
            $builder->withKingdomLengthError();
        }

        if ($equipmentId <= 0) {
            $builder->withEquipmentIdError();
        }

        if ($factionId <= 0) {
            $builder->withFactionIdError();
        }

        $exception = $builder->build();
        if ($exception->getErrors()) {
            throw $exception;
        }
    }
} 