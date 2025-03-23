<?php

namespace App\Faction\Domain\Service;

use App\Faction\Domain\Exception\FactionValidationException;

class FactionValidator
{
    public function validate(array $data): void
    {
        $errors = [];

        // Validar campos requeridos
        $requiredFields = ['faction_name', 'description'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[] = "El campo {$field} es requerido";
            }
        }

        if (isset($data['faction_name'])) {
            if (!is_string($data['faction_name'])) {
                $errors[] = "El nombre de la facción debe ser texto";
            } elseif (strlen($data['faction_name']) > 100) {
                $errors[] = "El nombre de la facción no puede exceder los 100 caracteres";
            }
        }

        if (isset($data['description'])) {
            if (!is_string($data['description'])) {
                $errors[] = "La descripción debe ser texto";
            } elseif (strlen($data['description']) > 255) {
                $errors[] = "La descripción no puede exceder los 255 caracteres";
            }
        }

        if (count($errors) > 0) {
            throw new FactionValidationException($errors);
        }
    }
    
}