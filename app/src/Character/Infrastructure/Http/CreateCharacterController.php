<?php

namespace App\Character\Infrastructure\Http;

use App\Character\Application\CreateCharacterUseCase;
use App\Character\Domain\CharacterToArrayTransformer;
use App\Character\Domain\Exception\CharacterValidationException;
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

        # Validamos los campos requeridos
        $requiredFields = ['name', 'birth_date', 'kingdom', 'equipment_id', 'faction_id'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $response->getBody()->write(json_encode([
                    'error' => "Campo requerido: {$field}"
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
            
            # Devolvemos el personaje creado
            $response->getBody()->write(json_encode([
                'character' => CharacterToArrayTransformer::transform($character),
                'message' => 'El personaje se ha creado correctamente'
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (CharacterValidationException $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage() 
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);

        }
    }
}
