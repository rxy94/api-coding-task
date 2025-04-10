<?php

use App\Character\Infrastructure\Http\CreateCharacterController;
use App\Character\Infrastructure\Http\ReadCharacterController;
use App\Character\Infrastructure\Http\ReadCharacterByIdController;
use App\Character\Infrastructure\Http\UpdateCharacterController;
use App\Character\Infrastructure\Http\DeleteCharacterByIdController;
use App\Equipment\Infrastructure\Http\CreateEquipmentController;
use App\Equipment\Infrastructure\Http\DeleteEquipmentByIdController;
use App\Equipment\Infrastructure\Http\ReadEquipmentByIdController;
use App\Equipment\Infrastructure\Http\ReadEquipmentController;
use App\Equipment\Infrastructure\Http\UpdateEquipmentController;
use App\Faction\Infrastructure\Http\CreateFactionController;
use App\Faction\Infrastructure\Http\DeleteFactionByIdController;
use App\Faction\Infrastructure\Http\ReadFactionByIdController;
use App\Faction\Infrastructure\Http\ReadFactionController;
use App\Faction\Infrastructure\Http\UpdateFactionController;
use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {

    # Rutas para personajes
    $app->get('/characters', ReadCharacterController::class);
    $app->get('/characters/{id}', ReadCharacterByIdController::class);
    $app->post('/characters', CreateCharacterController::class);
    $app->put('/characters/{id}', UpdateCharacterController::class); # usar x-www-form-urlencoded en vez de form-data
    $app->delete('/characters/{id}', DeleteCharacterByIdController::class);

    # Rutas para facciones
    $app->get('/factions', ReadFactionController::class);
    $app->get('/factions/{id}', ReadFactionByIdController::class);
    $app->post('/factions', CreateFactionController::class);
    $app->put('/factions/{id}', UpdateFactionController::class);
    $app->delete('/factions/{id}', DeleteFactionByIdController::class);

    # Rutas para equipamientos
    $app->get('/equipments', ReadEquipmentController::class);
    $app->get('/equipments/{id}', ReadEquipmentByIdController::class);
    $app->post('/equipments', CreateEquipmentController::class);
    $app->put('/equipments/{id}', UpdateEquipmentController::class);
    $app->delete('/equipments/{id}', DeleteEquipmentByIdController::class);

    # Manejamos las rutas no encontradas
    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function (Request $request, Response $response) {
        $response->getBody()->write(json_encode([
            "error" => "Ruta no encontrada",
            "path" => $request->getUri()->getPath()
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    });

};