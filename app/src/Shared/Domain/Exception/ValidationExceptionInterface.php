<?php

namespace App\Shared\Domain\Exception;

interface ValidationExceptionInterface
{
    public function getErrors(): array;
} 