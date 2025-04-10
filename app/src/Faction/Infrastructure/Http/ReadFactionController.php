<?php

namespace App\Faction\Infrastructure\Http;

use App\Faction\Application\ReadFactionUseCase;
use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionToArrayTransformer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReadFactionController {

    private const SUCCESS_MESSAGE = 'Facciones obtenidas correctamente';
    private const ERROR_MESSAGE = 'Error al obtener las facciones';

    public function __construct(
        private ReadFactionUseCase $readFactionUseCase
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
            $factions = $this->readFactionUseCase->execute();

            $response->getBody()->write(json_encode([
            'factions' => array_map(
                function (Faction $faction) {
                    return FactionToArrayTransformer::transform($faction);
                },
                $factions
            ),
            'message' => self::SUCCESS_MESSAGE
            ]));

            return $response->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => self::ERROR_MESSAGE,
                'message' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

}