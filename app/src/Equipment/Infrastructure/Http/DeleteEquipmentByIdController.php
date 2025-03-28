<?php

namespace App\Equipment\Infrastructure\Http;

use App\Equipment\Application\DeleteEquipmentUseCase;
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
                'message' => 'Equipo eliminado correctamente'
            ]));

            return $response->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Error al eliminar el equipo',
                'message' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
