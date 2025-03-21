<?php

namespace App\Character\Infrastructure\Http;

use App\Character\Application\CreateCharacterUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CreateCharacterController
{
    public function __construct(private CreateCharacterUseCase $useCase)
    {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();

        // Validate required fields
        $requiredFields = ['name', 'birth_date', 'kingdom', 'equipment_id', 'faction_id'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $response->getBody()->write(json_encode([
                    'error' => "Missing required field: {$field}"
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }
        
        try {
            $character = $this->useCase->execute(
                $data['name'],
                $data['birth_date'],
                $data['kingdom'],
                $data['equipment_id'],
                $data['faction_id']
            );
            
            // Return success response
            $response->getBody()->write(json_encode([
                'id' => $character->getId(),
                'message' => 'Character created successfully'
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Failed to create character',
                'message' => $e->getMessage()
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
