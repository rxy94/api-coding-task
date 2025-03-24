<?php

namespace App\Faction\Infrastructure\Http;

use App\Faction\Application\ReadFactionUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReadFactionController {

    public function __construct(private ReadFactionUseCase $readFactionUseCase)
    {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $factions = $this->readFactionUseCase->execute();

        $factionsArray = array_map(function($faction) {     
            return $faction->toArray();
        }, $factions);

        $response->getBody()->write(json_encode([
            'data' => $factionsArray,
            'message' => 'Las facciones han sido obtenidas correctamente'
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }

}