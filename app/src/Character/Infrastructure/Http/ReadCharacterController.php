<?php

namespace App\Character\Infrastructure\Http;

use App\Character\Application\ReadCharacterUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReadCharacterController {

    public function __construct(private ReadCharacterUseCase $readCharacterUseCase)
    {
    }

    public function __invoke(Request $request, Response $response): Response 
    {
        try {
            $characters = $this->readCharacterUseCase->execute();
                
            $response->getBody()->write(json_encode([
                'data' => $characters,
                'message' => 'Personajes obtenidos correctamente'
            ]));
                
            //dump(json_last_error_msg());
            return $response->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            error_log("Error en el controlador: " . $e->getMessage());
            $response->getBody()->write(json_encode([
                'error' => 'Error al obtener los personajes',
                'message' => $e->getMessage()

            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
