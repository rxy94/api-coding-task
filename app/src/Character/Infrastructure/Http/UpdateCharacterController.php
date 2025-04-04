<?php

namespace App\Character\Infrastructure\Http;

use App\Character\Application\UpdateCharacterUseCase;
use App\Character\Application\UpdateCharacterUseCaseRequest;
use App\Character\Domain\Exception\CharacterValidationException;
use App\Character\Infrastructure\Persistence\Pdo\Exception\CharacterNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UpdateCharacterController
{
    public function __construct(
        private UpdateCharacterUseCase $updateCharacterUseCase
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {   
        # Obtenemos los datos del cuerpo de la petición
        $data = $request->getParsedBody();

        $requiredFields = ['name', 'birth_date', 'kingdom', 'equipment_id', 'faction_id'];
        
        foreach ($requiredFields as $field){
            if (!isset($data[$field])){
                $response->getBody()->write(json_encode(['error' => "Missing required field: {$field}"]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }
        
        try {
            $useCaseRequest = new UpdateCharacterUseCaseRequest(
                id: $args['id'],
                name: $data['name'],
                birthDate: $data['birth_date'],
                kingdom: $data['kingdom'],
                equipmentId: $data['equipment_id'],
                factionId: $data['faction_id']
            );

            $useCaseResponse = $this->updateCharacterUseCase->execute($useCaseRequest);
            
            # Devolvemos el personaje actualizado
            $response->getBody()->write(json_encode([
                'id' => $useCaseResponse->getId(),
                'message' => 'Character updated successfully'
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (CharacterValidationException $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage() 
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } catch (CharacterNotFoundException $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage() 
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    }
} 