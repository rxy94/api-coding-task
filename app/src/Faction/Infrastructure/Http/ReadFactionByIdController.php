<?php

namespace App\Faction\Infrastructure\Http;

use App\Faction\Application\ReadFactionByIdUseCase;
use App\Faction\Domain\FactionToArrayTransformer;
use App\Faction\Domain\Exception\FactionNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReadFactionByIdController {

    private const SUCCESS_MESSAGE = 'Facción encontrada correctamente';

    public function __construct(
        private ReadFactionByIdUseCase $readFactionByIdUseCase
    ) {
    }

    public static function getSuccessMessage(): string
    {
        return self::SUCCESS_MESSAGE;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];

        try {
            $faction = $this->readFactionByIdUseCase->execute($id);

            $response->getBody()->write(json_encode([
                'faction' => FactionToArrayTransformer::transform($faction),
                'message' => self::SUCCESS_MESSAGE
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (FactionNotFoundException $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    }
}