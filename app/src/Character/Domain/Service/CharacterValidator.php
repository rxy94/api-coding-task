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
        $errors = [];

        # Validamos los campos requeridos
        if (empty($name)) {
            $errors[] = "El nombre es requerido";
        } elseif (strlen($name) > 100) {
            $errors[] = "El nombre no puede exceder los 100 caracteres";
        }

        if (empty($birthDate)) {
            $errors[] = "La fecha de nacimiento es requerida";
        } else {
            $date = \DateTime::createFromFormat('Y-m-d', $birthDate);
            if (!$date || $date->format('Y-m-d') !== $birthDate) {
                $errors[] = "La fecha de nacimiento debe tener el formato YYYY-MM-DD";
            }
        }

        if (empty($kingdom)) {
            $errors[] = "El reino es requerido";
        } elseif (strlen($kingdom) > 100) {
            $errors[] = "El reino no puede exceder los 100 caracteres";
        }

        if ($equipmentId <= 0) {
            $errors[] = "El ID del equipamiento debe ser un número positivo";
        }

        if ($factionId <= 0) {
            $errors[] = "El ID de la facción debe ser un número positivo";
        }

        if (!empty($errors)) {
            throw CharacterValidationException::fromErrors($errors);
        }
    }
} 