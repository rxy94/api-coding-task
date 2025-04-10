<?php

namespace App\Character\Infrastructure\Http;

use App\Character\Application\DeleteCharacterUseCase;
use App\Character\Domain\Exception\CharacterNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DeleteCharacterByIdController
{
    private const SUCCESS_MESSAGE = 'Personaje eliminado correctamente';
    private const ERROR_MESSAGE = 'Error al eliminar el personaje';

    public function __construct(
        private DeleteCharacterUseCase $deleteCharacterUseCase
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

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $this->deleteCharacterUseCase->execute($id);

            $response->getBody()->write(json_encode([
                'message' => self::SUCCESS_MESSAGE
            ]));

            return $response->withHeader('Content-Type', 'application/json');

        } catch (CharacterNotFoundException $e) {
            $response->getBody()->write(json_encode([
                'error' => self::ERROR_MESSAGE,
                'message' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'message' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}