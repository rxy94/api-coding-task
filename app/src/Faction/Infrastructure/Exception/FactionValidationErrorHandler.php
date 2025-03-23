<?php

namespace App\Faction\Infrastructure\Exception;

use App\Faction\Domain\Exception\FactionValidationException;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Throwable;

class FactionValidationErrorHandler
{
    public function __construct(private App $app)
    {
    }

    public function handle(Throwable $exception): ResponseInterface
    {
        $response = $this->app->getResponseFactory()->createResponse();
        
        if ($exception instanceof FactionValidationException) {
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