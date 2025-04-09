<?php

namespace App\Character\Infrastructure\Http;

use App\Character\Application\ReadCharacterByIdUseCase;
use App\Character\Domain\CharacterToArrayTransformer;
use App\Character\Domain\Exception\CharacterNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReadCharacterByIdController {

    public function __construct(private ReadCharacterByIdUseCase $readCharacterByIdUseCase)
    {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];

        try {
            $character = $this->readCharacterByIdUseCase->execute($id);

            $response->getBody()->write(json_encode([
                'character' => CharacterToArrayTransformer::transform($character),
                'message' => 'Personaje obtenido correctamente'
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (CharacterNotFoundException $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    }
}
