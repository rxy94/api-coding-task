<?php

namespace App\Equipment\Infrastructure\Http;

use App\Equipment\Application\CreateEquipmentUseCase;
use App\Equipment\Domain\Exception\EquipmentValidationException;
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
        
        # Validamos los campos requeridos
        $requiredFields = ['name', 'type', 'made_by'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $response->getBody()->write(json_encode([
                    'error' => "Campo requerido: {$field}"
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }
        
        try {
            $equipment = $this->useCase->execute(
                $data['name'],
                $data['type'],
                $data['made_by']
            );

            # Devolvemos el id del equipamiento creado
            $response->getBody()->write(json_encode([
                'id' => $equipment->getId(),
                'message' => 'El equipamiento se ha creado correctamente'
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (EquipmentValidationException $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}