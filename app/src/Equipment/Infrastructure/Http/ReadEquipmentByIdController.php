<?php

namespace App\Equipment\Infrastructure\Http;

use App\Equipment\Application\ReadEquipmentByIdUseCase;
use App\Equipment\Infrastructure\Persistence\Pdo\MySQLEquipmentToArrayTransformer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReadEquipmentByIdController
{
    public function __construct(private ReadEquipmentByIdUseCase $useCase)
    {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $equipment = $this->useCase->execute($id);

        $response->getBody()->write(json_encode([
            'equipment' => MySQLEquipmentToArrayTransformer::transform($equipment),
            'message' => 'Equipamiento encontrado correctamente'
        ]));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
