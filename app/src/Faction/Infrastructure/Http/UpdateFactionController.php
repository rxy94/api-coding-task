<?php

namespace App\Faction\Infrastructure\Http;

use App\Faction\Application\UpdateFactionUseCase;
use App\Faction\Application\UpdateFactionUseCaseRequest;
use App\Faction\Domain\Exception\FactionValidationException;
use App\Faction\Domain\Exception\FactionNotFoundException;
use App\Faction\Domain\FactionToArrayTransformer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UpdateFactionController
{
    private UpdateFactionUseCase $updateFactionUseCase;
    private FactionToArrayTransformer $factionToArrayTransformer;

    public function __construct(
        UpdateFactionUseCase $updateFactionUseCase,
        FactionToArrayTransformer $factionToArrayTransformer
    ) {
        $this->updateFactionUseCase = $updateFactionUseCase;
        $this->factionToArrayTransformer = $factionToArrayTransformer;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();

        try {
            $id = (int) $args['id'];
            $data = json_decode($request->getBody()->getContents(), true);

            if (!isset($data['faction_name']) || !isset($data['description'])) {
                $response->getBody()->write(json_encode([
                    'error' => 'Los campos faction_name y description son requeridos'
                ]));

                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            $useCaseRequest = new UpdateFactionUseCaseRequest(
                id: $id,
                faction_name: $data['faction_name'],
                description: $data['description']
            );

            $useCaseResponse = $this->updateFactionUseCase->execute($useCaseRequest);

            $response->getBody()->write(json_encode([
                'faction' => FactionToArrayTransformer::transform($useCaseResponse->getFaction()),
                'message' => 'La facción se ha actualizado correctamente'
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (FactionValidationException $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);

        } catch (FactionNotFoundException $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Error inesperado',
                'message' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
