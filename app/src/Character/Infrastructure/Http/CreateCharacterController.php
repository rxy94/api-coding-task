<?php

namespace App\Character\Infrastructure\Http;

use App\Character\Application\CreateCharacterUseCase;
use App\Character\Application\CreateCharacterUseCaseRequest;
use App\Character\Domain\CharacterToArrayTransformer;
use App\Character\Domain\Exception\CharacterValidationException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CreateCharacterController
{
    public function __construct(
        private CreateCharacterUseCase $createCharacterUseCase
    ) {
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
            $useCaseRequest = new CreateCharacterUseCaseRequest(
                $data['name'],
                $data['birth_date'],
                $data['kingdom'],
                (int) $data['equipment_id'],
                (int) $data['faction_id']
            );

            $useCaseResponse = $this->createCharacterUseCase->execute($useCaseRequest);
            
            # Devolvemos el personaje creado
            $response->getBody()->write(json_encode([
                'character' => CharacterToArrayTransformer::transform($useCaseResponse->getCharacter()),
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
