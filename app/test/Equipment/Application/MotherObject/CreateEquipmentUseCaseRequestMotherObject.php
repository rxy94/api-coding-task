<?php

namespace App\Test\Equipment\Application\MotherObject;

use App\Equipment\Application\CreateEquipmentUseCaseRequest;

class CreateEquipmentUseCaseRequestMotherObject
{
    public static function valid(): CreateEquipmentUseCaseRequest
    {
        return new CreateEquipmentUseCaseRequest(
            'Sword of Destiny',
            'A legendary sword',
            'John Doe',
        );
    }

    public static function withInvalidName(): CreateEquipmentUseCaseRequest
    {
        return new CreateEquipmentUseCaseRequest(
            '',
            'A legendary sword',
            'John Doe',
        );
    }

    public static function withInvalidLengthName(): CreateEquipmentUseCaseRequest
    {
        return new CreateEquipmentUseCaseRequest(
            str_repeat('a', 256),
            'A legendary sword',
            'John Doe',
        );
    }

    public static function withInvalidType(): CreateEquipmentUseCaseRequest
    {
        return new CreateEquipmentUseCaseRequest(
            'Sword of Destiny',
            '',
            'John Doe',
        );
    }

    public static function withInvalidLengthType(): CreateEquipmentUseCaseRequest
    {
        return new CreateEquipmentUseCaseRequest(
            'Sword of Destiny',
            str_repeat('a', 256),
            'John Doe',
        );
    }

    public static function withInvalidMadeBy(): CreateEquipmentUseCaseRequest
    {
        return new CreateEquipmentUseCaseRequest(
            'Sword of Destiny',
            'A legendary sword',
            '',
        );
    }

    public static function withInvalidLengthMadeBy(): CreateEquipmentUseCaseRequest
    {
        return new CreateEquipmentUseCaseRequest(
            'Sword of Destiny',
            'A legendary sword',
            str_repeat('a', 256),
        );
    }

    public static function withInvalidId(): CreateEquipmentUseCaseRequest
    {
        return new CreateEquipmentUseCaseRequest(
            'Sword of Destiny',
            'A legendary sword',
            'John Doe',
            -1
        );
    }
} 