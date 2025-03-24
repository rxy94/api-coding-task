<?php

namespace App\Shared\Infrastructure\Exception;

use App\Shared\Domain\Exception\ValidationExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Throwable;

class ValidationErrorHandler
{
    # Inyectamos la interfaz de Slim ResponseFactoryInterface para crear la respuesta en vez de pasarle el $app entero
    public function __construct(private ResponseFactoryInterface $responseFactory)
    {
    }

    public function handle(Throwable $exception): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();
        
        if ($exception instanceof ValidationExceptionInterface) { # Se mostrarán los errores de validación en postman
            $response->getBody()->write(json_encode([
                'error' => $exception->getMessage(),
                'messages' => $exception->getErrors(),
            ]));

        } else {
            $response->getBody()->write(json_encode([
                'error' => 'Error desconocido',
            ]));

        }
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }
}