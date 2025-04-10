<?php

namespace App\Faction\Infrastructure\Http;

use App\Faction\Application\DeleteFactionUseCase;
use App\Faction\Domain\Exception\FactionNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DeleteFactionByIdController
{
    private const SUCCESS_MESSAGE = 'Facción eliminada correctamente';
    private const ERROR_MESSAGE = 'Error al eliminar la facción';

    public function __construct(
        private DeleteFactionUseCase $deleteFactionUseCase
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
            $this->deleteFactionUseCase->execute($id);

            $response->getBody()->write(json_encode([
                'message' => self::SUCCESS_MESSAGE
            ]));

            return $response->withHeader('Content-Type', 'application/json');

        } catch (FactionNotFoundException $e) {
            $response->getBody()->write(json_encode([
                'error' => self::ERROR_MESSAGE,
                'message' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => self::ERROR_MESSAGE,
                'message' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
