<?php

namespace App\Character\Infrastructure\Http;

use App\Character\Application\DeleteCharacterUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DeleteCharacterByIdController
{
    public function __construct(private DeleteCharacterUseCase $deleteCharacterUseCase)
    {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $this->deleteCharacterUseCase->execute($id);

            $response->getBody()->write(json_encode([
                'message' => 'Personaje eliminado correctamente'
            ]));

            return $response->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Error al eliminar el personaje',
                'message' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}