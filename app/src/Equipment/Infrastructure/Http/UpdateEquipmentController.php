<?php

namespace App\Equipment\Infrastructure\Http;

use App\Equipment\Application\UpdateEquipmentUseCase;
use App\Equipment\Domain\Exception\EquipmentValidationException;
use App\Equipment\Domain\Exception\EquipmentNotFoundException;
use App\Equipment\Application\UpdateEquipmentUseCaseRequest;
use App\Equipment\Domain\EquipmentToArrayTransformer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UpdateEquipmentController
{
    private const SUCCESS_MESSAGE = 'Equipo actualizado correctamente';
    private const ERROR_MESSAGE = 'Error al actualizar el equipo';

    public function __construct(
        private UpdateEquipmentUseCase $updateEquipmentUseCase
    ) {
    }

    public static function getSuccessMessage(): string
    {
        return self::SUCCESS_MESSAGE;
    }

    public static function getErrorMessage(): string
    {
        return self::ERROR_MESSAGE;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();

        $requiredFields = ['name', 'type', 'made_by'];

        foreach ($requiredFields as $field){
            if (!isset($data[$field])){
                $response->getBody()->write(json_encode(['error' => "Campo requerido: {$field}"]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }

        try {
            $id = (int) $args['id'];

            $useCaseRequest = new UpdateEquipmentUseCaseRequest(
                id: $id,
                name: $data['name'],
                type: $data['type'],
                madeBy: $data['made_by']
            );

            $useCaseResponse = $this->updateEquipmentUseCase->execute($useCaseRequest);
            
            $response->getBody()->write(json_encode([
                'equipment' => EquipmentToArrayTransformer::transform($useCaseResponse->getEquipment()),
                'message' => self::SUCCESS_MESSAGE
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

        } catch (\Exception $e){
            $response->getBody()->write(json_encode([
                'error' => self::ERROR_MESSAGE,
                'message' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
    
}
