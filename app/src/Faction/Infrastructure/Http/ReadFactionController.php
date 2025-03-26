<?php

namespace App\Faction\Infrastructure\Http;

use App\Faction\Application\ReadFactionUseCase;
use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionToArrayTransformer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReadFactionController {

    public function __construct(private ReadFactionUseCase $useCase)
    {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $factions = $this->useCase->execute();

        $response->getBody()->write(json_encode([
            'factions' => array_map(
                function (Faction $faction) {
                    return FactionToArrayTransformer::transform($faction);
                },
                $factions
            ),
            'message' => 'Facciones obtenidas correctamente'
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }

}