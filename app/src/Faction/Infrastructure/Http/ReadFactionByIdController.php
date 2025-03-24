<?php

namespace App\Faction\Infrastructure\Http;

use App\Faction\Application\ReadFactionByIdUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReadFactionByIdController {

    public function __construct(private ReadFactionByIdUseCase $readFactionByIdUseCase)
    {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $id = $request->getAttribute('id');
        $faction = $this->readFactionByIdUseCase->execute($id);

        $response->getBody()->write(json_encode([
            'data' => $faction->toArray(),
            'message' => 'La facción ha sido obtenida correctamente'
        ]));

        return $response->withHeader('Content-Type', 'application/json');

    }
}