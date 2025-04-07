<?php

namespace App\Equipment\Infrastructure\Http;

use App\Equipment\Application\UpdateEquipmentUseCase;
use App\Equipment\Domain\Exception\EquipmentValidationException;
use App\Equipment\Infrastructure\Persistence\Pdo\Exception\EquipmentNotFoundException;
use App\Equipment\Application\UpdateEquipmentUseCaseRequest;
use App\Equipment\Domain\EquipmentToArrayTransformer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UpdateEquipmentController
{
    public function __construct(
        private UpdateEquipmentUseCase $updateEquipmentUseCase
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();

        $requiredFields = ['name', 'type', 'made_by'];

        foreach ($requiredFields as $field){
            if (!isset($data[$field])){
                $response->getBody()->write(json_encode(['error' => "Missing required field: {$field}"]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }

        try {
            $useCaseRequest = new UpdateEquipmentUseCaseRequest(
                id: $args['id'],
                name: $data['name'],
                type: $data['type'],
                madeBy: $data['made_by']
            );

            $useCaseResponse = $this->updateEquipmentUseCase->execute($useCaseRequest);
            
            $response->getBody()->write(json_encode([
                'equipment' => EquipmentToArrayTransformer::transform($useCaseResponse->getEquipment()),
                'message' => 'El equipamiento se ha actualizado correctamente'
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (EquipmentValidationException $e){
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } catch (EquipmentNotFoundException $e){
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    }
    
}
