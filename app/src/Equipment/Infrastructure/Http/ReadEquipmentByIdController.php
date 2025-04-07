<?php

namespace App\Equipment\Infrastructure\Http;

use App\Equipment\Application\ReadEquipmentByIdUseCase;
use App\Equipment\Domain\EquipmentToArrayTransformer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReadEquipmentByIdController
{
    private const SUCCESS_MESSAGE = 'Equipamiento encontrado correctamente';

    public function __construct(private ReadEquipmentByIdUseCase $useCase)
    {
    }

    public static function getSuccessMessage(): string
    {
        return self::SUCCESS_MESSAGE;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $equipment = $this->useCase->execute($id);

        $response->getBody()->write(json_encode([
            'equipment' => EquipmentToArrayTransformer::transform($equipment),
            'message' => self::SUCCESS_MESSAGE
        ]));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
