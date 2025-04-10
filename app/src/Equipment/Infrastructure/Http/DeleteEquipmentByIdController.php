<?php

namespace App\Equipment\Infrastructure\Http;

use App\Equipment\Application\DeleteEquipmentUseCase;
use App\Equipment\Domain\Exception\EquipmentNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DeleteEquipmentByIdController
{
    private const SUCCESS_MESSAGE = 'Equipo eliminado correctamente';
    private const ERROR_MESSAGE = 'Error al eliminar el equipo';

    public function __construct(
        private DeleteEquipmentUseCase $deleteEquipmentUseCase
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
            $this->deleteEquipmentUseCase->execute($id);

            $response->getBody()->write(json_encode([
                'message' => self::SUCCESS_MESSAGE
            ]));

            return $response->withHeader('Content-Type', 'application/json');

        } catch (EquipmentNotFoundException $e) {
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
