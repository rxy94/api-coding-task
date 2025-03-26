<?php

namespace App\Equipment\Infrastructure\Http;

use App\Equipment\Application\ReadEquipmentUseCase;
use App\Equipment\Domain\Equipment;
use App\Equipment\Infrastructure\Persistence\Pdo\MySQLEquipmentToArrayTransformer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReadEquipmentController {

    public function __construct(private ReadEquipmentUseCase $useCase)
    {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $equipments = $this->useCase->execute();

        $response->getBody()->write(json_encode([
            'equipments' => array_map(
                function (Equipment $equipment) {
                    return MySQLEquipmentToArrayTransformer::transform($equipment);
                },
                $equipments
            ),
            'message' => 'Equipos obtenidos correctamente'
        ]));
            
        return $response->withHeader('Content-Type', 'application/json');
        
    }
}