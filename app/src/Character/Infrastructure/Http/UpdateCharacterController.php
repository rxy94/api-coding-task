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
    private UpdateCharacterUseCase $updateCharacterUseCase;
    private CharacterToArrayTransformer $characterToArrayTransformer;

    public function __construct(
        UpdateCharacterUseCase $updateCharacterUseCase,
        CharacterToArrayTransformer $characterToArrayTransformer
    ) {
        $this->updateCharacterUseCase = $updateCharacterUseCase;
        $this->characterToArrayTransformer = $characterToArrayTransformer;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $data = json_decode($request->getBody()->getContents(), true);

            if (!isset($data['name']) || !isset($data['faction_id']) || !isset($data['equipment_id'])) {
                $response->getBody()->write(json_encode([
                    'error' => 'Los campos name, faction_id y equipment_id son requeridos'
                ]));

                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

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
                'character' => $this->characterToArrayTransformer->transform($character),
                'message' => 'El personaje se ha actualizado correctamente'
            ]));

            return $response->withHeader('Content-Type', 'application/json');

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
                'error' => 'Error al actualizar el personaje',
                'message' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
