<?php

namespace App\Equipment\Domain\Service;

use App\Equipment\Domain\Exception\EquipmentValidationException;

class EquipmentValidator
{
    public function validate(
        string $name,
        string $type,
        string $madeBy
    ): void {

        # Validamos los campos requeridos   

        if (empty($name)) {
            throw EquipmentValidationException::withNameError();
        } elseif (strlen($name) > 100) {
            throw EquipmentValidationException::withNameLengthError();
        }

        if (empty($type)) {
            throw EquipmentValidationException::withTypeError();
        } elseif (strlen($type) > 100) {
            throw EquipmentValidationException::withTypeErrorLengthError();
        }

        if (empty($madeBy)) {
            throw EquipmentValidationException::withMadeByError();
        } elseif (strlen($madeBy) > 100) {
            throw EquipmentValidationException::withMadeByLengthError();
        }

    }
}