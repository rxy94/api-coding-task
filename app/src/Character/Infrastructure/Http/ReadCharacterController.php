<?php

namespace App\Character\Infrastructure\Http;

use App\Character\Application\ReadCharacterUseCase;
use App\Character\Domain\Character;
use App\Character\Domain\CharacterToArrayTransformer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReadCharacterController {

    private const SUCCESS_MESSAGE = 'Personajes obtenidos correctamente'; 

    public function __construct(
        private ReadCharacterUseCase $readCharacterUseCase
    ) {
    }

    public static function getSuccessMessage(): string
    {
        return self::SUCCESS_MESSAGE;
    }

    public function __invoke(Request $request, Response $response): Response 
    {
        try {
            $characters = $this->readCharacterUseCase->execute();

            $response->getBody()->write(json_encode([
                'characters' => array_map(
                    fn(Character $character) => CharacterToArrayTransformer::transform($character),
                    $characters
                ),
                'message' => self::SUCCESS_MESSAGE
            ]));

            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            error_log("Error en el controlador: " . $e->getMessage());
            $response->getBody()->write(json_encode([
                'message' => $e->getMessage() 
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
