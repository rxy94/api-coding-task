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
        $builder = EquipmentValidationException::builder();

        if (empty($name)) {
            $builder->withNameError();
        } elseif (strlen($name) > 100) {
            $builder->withNameLengthError();
        }

        if (empty($type)) {
            $builder->withTypeError();
        } elseif (strlen($type) > 100) {
            $builder->withTypeError();
        }

        if (empty($madeBy)) {
            $builder->withMadeByError();
        } elseif (strlen($madeBy) > 100) {
            $builder->withMadeByLengthError();
        }

        $exception = $builder->build();

        if ($exception->getErrors()) {
            throw $exception;
        }

    }
}