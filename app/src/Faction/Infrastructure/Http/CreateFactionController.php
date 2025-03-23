<?php

namespace App\Faction\Infrastructure\Http;

use App\Faction\Application\CreateFactionUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CreateFactionController
{
    public function __construct(private CreateFactionUseCase $useCase)
    {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        
        // Validate required fields
        $requiredFields = ['faction_name', 'description'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $response->getBody()->write(json_encode([
                    'error' => "Missing required field: {$field}"
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }
        
        try {
            $faction = $this->useCase->execute(
                $data['faction_name'],
                $data['description']
            );

            // Return success response
            $response->getBody()->write(json_encode([
                'id' => $faction->getId(),
                'message' => 'La facción se ha creado correctamente'
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Error al crear la facción',
                'message' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

}
