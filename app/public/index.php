<?php

require_once __DIR__ . '/database.php';
require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use DI\ContainerBuilder;

use App\Controller\CreateCharacterController;

# Creamos el contenedor de dependencias
$containerBuilder = new ContainerBuilder();

# Añadimos las definiciones al contenedor
$containerBuilder->addDefinitions([
    PDO::class => function () {
        return new PDO('mysql:host=db;dbname=lotr', 'root', 'root', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    },
    CreateCharacterController::class => function ($container) {
        return new CreateCharacterController($container->get(PDO::class));
    }
]);

# Creamos el contenedor
$container = $containerBuilder->build();

# Creamos la aplicación con el contenedor
$app = AppFactory::createFromContainer($container);

# Ruta por defecto
$app->get('/', function (Request $request, Response $response) use ($container) {
    $pdo = $container->get(PDO::class);
    $query = $pdo->query('SELECT * FROM characters');
    $characters = $query->fetchAll();

    $response->getBody()->write(json_encode($characters));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

});

# Ruta para crear un nuevo personaje
$app->post('/characters', CreateCharacterController::class);

# Manejamos las rutas no encontradas
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function (Request $request, Response $response) {
    $response->getBody()->write("Ruta no encontrada");
    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
});

$app->run();

