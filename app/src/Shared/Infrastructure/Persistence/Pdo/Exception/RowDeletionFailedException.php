<?php

namespace App\Shared\Infrastructure\Persistence\Pdo\Exception;

class RowDeletionFailedException extends \Exception
{
    private const MESSAGE = "Error al eliminar la fila";

    public static function build(): self
    {
        return new self(self::MESSAGE);
    }
}
