<?php

namespace App\Equipment\Infrastructure\Http;

use App\Equipment\Application\CreateEquipmentUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CreateEquipmentController
{
    public function __construct(private CreateEquipmentUseCase $useCase)
    {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        
        //TODO: Validar los campos requeridos
        
        try {
            $equipment = $this->useCase->execute(
                $data['name'],
                $data['type'],
                $data['made_by']
            );

            // Devolvemos una respuesta de éxito
            $response->getBody()->write(json_encode([
                'id' => $equipment->getId(),
                'message' => 'El equipamiento se ha creado correctamente'
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}