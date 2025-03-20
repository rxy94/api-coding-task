<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Database/Database.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use DI\ContainerBuilder;
use App\Controller\CreateCharacterController;
use App\Controller\CreateEquipmentController;
use App\Controller\CreateFactionController;
use App\Database\Database;

# Creamos el contenedor de dependencias
$containerBuilder = new ContainerBuilder();

# Añadimos las definiciones al contenedor
$containerBuilder->addDefinitions([
    PDO::class => function () {
        return Database::getInstance()->getConnection();
    },
    CreateCharacterController::class => function ($container) {
        return new CreateCharacterController($container->get(PDO::class));
    },
    CreateFactionController::class => function ($container) {
        return new CreateFactionController($container->get(PDO::class));
    },
    CreateEquipmentController::class => function ($container) {
        return new CreateEquipmentController($container->get(PDO::class));
    }
]);

# Creamos el contenedor
$container = $containerBuilder->build();

# Creamos la aplicación con el contenedor
$app = AppFactory::createFromContainer($container);

# Ruta por defecto
$app->get('/', function (Request $request, Response $response) use ($container) {
    try {
        $pdo = $container->get(PDO::class);
        $query = $pdo->query('SELECT * FROM characters');
        $characters = $query->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode([
            'data'=> $characters,
            'message' => 'Personajes obtenidos correctamente'
        ]));
        
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (\Exception $e) {
        $response->getBody()->write(json_encode([
            'error' => 'Error al obtener los personajes',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]));
        
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

# Ruta para obtener todas las facciones
$app->get('/factionsList', function (Request $request, Response $response) use ($container) {
    try {
        $pdo = $container->get(PDO::class);
        
        // Intentar obtener las facciones directamente con PDO para verificar
        $query = $pdo->query('SELECT * FROM factions');
        $factions = $query->fetchAll(PDO::FETCH_ASSOC);
        
        $response->getBody()->write(json_encode([
            'data' => $factions,
            'message' => 'Facciones obtenidas correctamente'
        ]));
        
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    } catch (\Exception $e) {
        $response->getBody()->write(json_encode([
            'error' => 'Error al obtener las facciones',
            'message' => $e->getMessage()
        ]));
        
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

# Ruta para obtener todos los equipamientos
$app->get('/equipmentsList', function (Request $request, Response $response) use ($container) {
    try {
        $pdo = $container->get(PDO::class);
        $query = $pdo->query('SELECT * FROM equipments');
        $equipments = $query->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode([
            'data' => $equipments,
            'message' => 'Equipamientos obtenidos correctamente'
        ]));            

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    } catch (\Exception $e) {
        $response->getBody()->write(json_encode([
            'error' => 'Error al obtener los equipamientos',
            'message' => $e->getMessage()
        ]));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

# Ruta para crear un nuevo personaje
$app->post('/characters', CreateCharacterController::class);

# Ruta para crear una nueva facción
$app->post('/factions', CreateFactionController::class);

# Ruta para crear un nuevo equipamiento
$app->post('/equipments', CreateEquipmentController::class);

# Manejamos las rutas no encontradas
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function (Request $request, Response $response) {
    $response->getBody()->write("Ruta no encontrada");
    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
});

$app->run();

