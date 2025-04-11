<?php

namespace App\Equipment\Infrastructure\Http;

use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\EquipmentToArrayTransformer;
use App\Equipment\Application\ReadEquipmentUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReadEquipmentController {

    private const SUCCESS_MESSAGE = 'Equipos obtenidos correctamente';
    private const ERROR_MESSAGE = 'Error al obtener los equipos';
    public function __construct(
        private ReadEquipmentUseCase $readEquipmentUseCase
    ) {
    }

    public static function getSuccessMessage(): string
    {
        return self::SUCCESS_MESSAGE;
    }

    public static function getErrorMessage(): string
    {
        return self::ERROR_MESSAGE;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        try {
            $equipments = $this->readEquipmentUseCase->execute();

            $response->getBody()->write(json_encode([
                'equipments' => array_map(
                    function (Equipment $equipment) {
                        return EquipmentToArrayTransformer::transform($equipment);
                    },
                    $equipments
                ),
                'message' => self::SUCCESS_MESSAGE
            ]));
                
            return $response->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'message' => self::ERROR_MESSAGE
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}