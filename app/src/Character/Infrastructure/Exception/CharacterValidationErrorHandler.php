<?php

namespace App\Character\Infrastructure\Exception;

use App\Character\Domain\Exception\CharacterValidationException;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Throwable;

class CharacterValidationErrorHandler
{
    public function __construct(private App $app)
    {
    }

    public function handle(Throwable $exception): ResponseInterface
    {
        $response = $this->app->getResponseFactory()->createResponse();

        if ($exception instanceof CharacterValidationException) {
            $response->getBody()->write(json_encode([
                'error' => 'Error de validación',
                'messages' => $exception->getErrors()
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