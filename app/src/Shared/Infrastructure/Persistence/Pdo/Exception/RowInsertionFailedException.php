<?php

namespace App\Shared\Infrastructure\Persistence\Pdo\Exception;

class RowInsertionFailedException extends \Exception
{
    private const MESSAGE = "Error al insertar la fila";

    public static function build(): self
    {
        return new self(self::MESSAGE);
    }
}