<?php

namespace App\Character\Domain\Service;

use App\Character\Domain\Exception\CharacterValidationException;

class CharacterValidator
{
    public function validate(array $data): void
    {
        $errors = [];

        // Validar campos requeridos
        $requiredFields = ['name', 'birth_date', 'kingdom', 'equipment_id', 'faction_id'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[] = "El campo {$field} es requerido";
            }
        }

        // Validar tipos y formatos
        if (isset($data['name'])) {
            if (!is_string($data['name'])) {
                $errors[] = "El nombre debe ser texto";
            } elseif (strlen($data['name']) > 100) {
                $errors[] = "El nombre no puede exceder los 100 caracteres";
            }
        }

        if (isset($data['birth_date'])) {
            $date = \DateTime::createFromFormat('Y-m-d', $data['birth_date']);
            if (!$date || $date->format('Y-m-d') !== $data['birth_date']) {
                $errors[] = "La fecha de nacimiento debe tener el formato YYYY-MM-DD";
            }
        }

        if (isset($data['kingdom'])) {
            if (!is_string($data['kingdom'])) {
                $errors[] = "El reino debe ser texto";
            } elseif (strlen($data['kingdom']) > 100) {
                $errors[] = "El reino no puede exceder los 100 caracteres";
            }
        }

        if (isset($data['equipment_id'])) {
            if (!is_numeric($data['equipment_id']) || $data['equipment_id'] <= 0) {
                $errors[] = "El ID del equipamiento debe ser un número positivo";
            }
        }

        if (isset($data['faction_id'])) {
            if (!is_numeric($data['faction_id']) || $data['faction_id'] <= 0) {
                $errors[] = "El ID de la facción debe ser un número positivo";
            }
        }

        if (!empty($errors)) {
            throw new CharacterValidationException($errors);
        }
    }
} 