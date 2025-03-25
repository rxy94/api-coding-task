<?php

namespace App\Equipment\Domain;

class EquipmentFactory
{
    public static function build(
        string $name,
        string $type,
        string $made_by,
        ?int $id = null
    ): Equipment
    {
        return new Equipment(
            $name,
            $type,
            $made_by,
            $id
        );
    }
}