<?php

namespace App\Character\Infrastructure\Http;

use App\Character\Application\ReadCharacterByIdUseCase;
use App\Character\Infrastructure\Persistence\Pdo\MySQLCharacterToArrayTransformer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReadCharacterByIdController {

    public function __construct(private ReadCharacterByIdUseCase $readCharacterByIdUseCase)
    {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $character = $this->readCharacterByIdUseCase->execute($id);

        $response->getBody()->write(json_encode([
            'character' => MySQLCharacterToArrayTransformer::transform($character),
            'message' => 'Personaje obtenido correctamente'
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }
}