<?php

namespace App\Faction\Infrastructure\Http;

use App\Faction\Application\ReadFactionByIdUseCase;
use App\Faction\Domain\FactionToArrayTransformer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReadFactionByIdController {

    public function __construct(private ReadFactionByIdUseCase $useCase)
    {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $faction = $this->useCase->execute($id);

        $response->getBody()->write(json_encode([
            'faction' => FactionToArrayTransformer::transform($faction),
            'message' => 'La facción ha sido obtenida correctamente'
        ]));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    }
}