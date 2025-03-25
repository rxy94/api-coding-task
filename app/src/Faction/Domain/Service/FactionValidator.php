<?php

namespace App\Faction\Domain\Service;

use App\Faction\Domain\Exception\FactionValidationException;

class FactionValidator
{
    public function validate(
        string $faction_name,
        string $description
    ): void
    {
        # Validamos los campos requeridos
        if (empty($faction_name)) {
            throw FactionValidationException::withFactionNameError();
        } elseif (strlen($faction_name) > 100) {
            throw FactionValidationException::withFactionNameLengthError();
        }

        if (empty($description)) {
            throw FactionValidationException::withDescriptionError();
        } elseif (strlen($description) > 255) {
            throw FactionValidationException::withDescriptionLengthError();
        }
    }
    
}