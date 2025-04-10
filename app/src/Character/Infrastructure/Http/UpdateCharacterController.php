<?php

namespace App\Character\Infrastructure\Http;

use App\Character\Application\UpdateCharacterUseCase;
use App\Character\Application\UpdateCharacterUseCaseRequest;
use App\Character\Domain\CharacterToArrayTransformer;
use App\Character\Domain\Exception\CharacterNotFoundException;
use App\Character\Domain\Exception\CharacterValidationException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UpdateCharacterController
{
    private const SUCCESS_MESSAGE = 'Personaje actualizado correctamente';
    private const ERROR_MESSAGE = 'Error al actualizar el personaje';

    public function __construct(
        private UpdateCharacterUseCase $updateCharacterUseCase,
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
        $data = $request->getParsedBody();

        $requiredFields = ['name', 'faction_id', 'equipment_id'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $response->getBody()->write(json_encode(['error' => "Campo requerido: {$field}"]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }

        try {
            $id = (int) $args['id'];

            $useCaseRequest = new UpdateCharacterUseCaseRequest(
                id: $id,
                name: $data['name'],
                birthDate: $data['birth_date'],
                kingdom: $data['kingdom'],
                equipmentId: $data['equipment_id'],
                factionId: $data['faction_id']
            );

            $character = $this->updateCharacterUseCase->execute($useCaseRequest);

            $response->getBody()->write(json_encode([
                'character' => CharacterToArrayTransformer::transform($character),
                'message' => self::SUCCESS_MESSAGE
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (CharacterValidationException $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);

        } catch (CharacterNotFoundException $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
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
