<?php

namespace App\Faction\Infrastructure\Http;

use App\Faction\Application\DeleteFactionUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DeleteFactionByIdController
{
    public function __construct(private DeleteFactionUseCase $deleteFactionUseCase)
    {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $this->deleteFactionUseCase->execute($id);

            $response->getBody()->write(json_encode([
                'message' => 'Facción eliminada correctamente'
            ]));

            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Error al eliminar la facción',
                'message' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
