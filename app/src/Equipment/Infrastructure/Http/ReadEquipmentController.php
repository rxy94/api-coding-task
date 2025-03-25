<?php

namespace App\Equipment\Infrastructure\Http;

use App\Equipment\Application\ReadEquipmentUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReadEquipmentController {

    public function __construct(private ReadEquipmentUseCase $readEquipmentUseCase)
    {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $equipments = $this->readEquipmentUseCase->execute();
        
        $equipmentsArray = array_map(function($equipment) {
            return $equipment->toArray();
        }, $equipments);    

        $response->getBody()->write(json_encode([
            'data' => $equipmentsArray,
            'message' => 'Equipos obtenidos correctamente'
        ]));

        return $response->withHeader('Content-Type', 'application/json');
        
    }
}