<?php

namespace App\Faction\Infrastructure\Http;

use App\Faction\Application\CreateFactionUseCase;
use App\Faction\Application\CreateFactionUseCaseRequest;
use App\Faction\Domain\FactionToArrayTransformer;
use App\Faction\Domain\Exception\FactionValidationException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CreateFactionController
{
    public function __construct(
        private CreateFactionUseCase $factionUseCase
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        
        # Validamos los campos requeridos
        $requiredFields = ['faction_name', 'description'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $response->getBody()->write(json_encode([
                    'error' => "Campo requerido: {$field}"
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }
        
        try {
            $useCaseRequest = new CreateFactionUseCaseRequest(
                $data['faction_name'],
                $data['description']
            );

            $useCaseResponse = $this->factionUseCase->execute($useCaseRequest);

            # Devolvemos una respuesta de éxito
            $response->getBody()->write(json_encode([
                'faction' => FactionToArrayTransformer::transform($useCaseResponse->getFaction()),
                'message' => 'La facción se ha creado correctamente'
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
            
        } catch (FactionValidationException $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

}
