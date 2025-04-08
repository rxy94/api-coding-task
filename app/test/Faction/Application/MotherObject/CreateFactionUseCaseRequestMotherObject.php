<?php

namespace App\Test\Faction\Application\MotherObject;

use App\Faction\Application\CreateFactionUseCaseRequest;

class CreateFactionUseCaseRequestMotherObject
{
    public static function valid(): CreateFactionUseCaseRequest
    {
        return new CreateFactionUseCaseRequest(
            'Kingdom of Spain',
            'A powerful kingdom in the south of Europe'
        );
    }

    public static function withInvalidName(): CreateFactionUseCaseRequest
    {
        return new CreateFactionUseCaseRequest(
            '',
            'A powerful kingdom in the south of Europe'
        );
    }

    public static function withInvalidLengthName(): CreateFactionUseCaseRequest
    {
        return new CreateFactionUseCaseRequest(
            str_repeat('a', 101),
            'A powerful kingdom in the south of Europe'
        );
    }

    public static function withInvalidDescription(): CreateFactionUseCaseRequest
    {
        return new CreateFactionUseCaseRequest(
            'Kingdom of Spain',
            ''
        );
    }

    public static function withInvalidLengthDescription(): CreateFactionUseCaseRequest
    {
        return new CreateFactionUseCaseRequest(
            'Kingdom of Spain',
            str_repeat('a', 256)
        );
    }

    public static function withInvalidId(): CreateFactionUseCaseRequest
    {
        return new CreateFactionUseCaseRequest(
            'Kingdom of Spain',
            'A powerful kingdom in the south of Europe',
            -1
        );
    }
} 