<?php

namespace App\Equipment\Infrastructure\Http;

use App\Equipment\Application\DeleteEquipmentUseCase;
use App\Equipment\Infrastructure\Persistence\Pdo\Exception\EquipmentNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DeleteEquipmentByIdController
{
    public function __construct(private DeleteEquipmentUseCase $deleteEquipmentUseCase)
    {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $this->deleteEquipmentUseCase->execute($id);

            $response->getBody()->write(json_encode([
                'message' => 'Equipamiento eliminado correctamente'
            ]));

            return $response->withHeader('Content-Type', 'application/json');

        } catch (EquipmentNotFoundException $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Equipamiento no encontrado'
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Error al eliminar el equipamiento',
                'message' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
