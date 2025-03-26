<?php

namespace App\Shared\Infrastructure\Persistence\Pdo\Exception;

class RowUpdateFailedException extends \Exception
{
    private const MESSAGE = 'Error al actualizar la fila';

    public static function build(): self
    {
        return new self(self::MESSAGE);
    }
}