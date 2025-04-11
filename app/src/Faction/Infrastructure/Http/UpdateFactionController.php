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
    private const SUCCESS_MESSAGE = 'Facción actualizada correctamente';

    public function __construct(
        private UpdateFactionUseCase $updateFactionUseCase,
    ) {
    }

    public static function getSuccessMessage(): string
    {
        return self::SUCCESS_MESSAGE;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();

        $requiredFields = ['faction_name', 'description'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $response->getBody()->write(json_encode(['error' => "Campo requerido: {$field}"]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }

        try {
            $id = (int) $args['id'];

            $useCaseRequest = new UpdateFactionUseCaseRequest(
                id: $id,
                faction_name: $data['faction_name'],
                description: $data['description']
            );

            $useCaseResponse = $this->updateFactionUseCase->execute($useCaseRequest);

            $response->getBody()->write(json_encode([
                'faction' => FactionToArrayTransformer::transform($useCaseResponse->getFaction()),
                'message' => self::SUCCESS_MESSAGE
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
                'message' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
