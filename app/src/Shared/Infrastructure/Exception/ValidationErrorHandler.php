<?php

namespace App\Shared\Infrastructure\Exception;

use Exception;

class ValidationErrorHandler extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function fromErrors(array $errors): self
    {
        return new self(json_encode($errors));
    }

    public static function fromMessage(string $message): self
    {
        return new self($message);
    }

}