<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use DI\ContainerBuilder;
use Dotenv\Dotenv;

use App\Controller\Character\CreateCharacterController;
use App\Controller\Character\ReadCharactersController;

use App\Controller\Equipment\CreateEquipmentController;
use App\Controller\Equipment\ReadEquipmetsController;

use App\Controller\Faction\CreateFactionController;
use App\Controller\Faction\ReadFactionsController;

# Creamos el contenedor de dependencias
$containerBuilder = new ContainerBuilder();

# Cargamos las variables de entorno
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

# Añadimos las definiciones al contenedor
$containerBuilder->addDefinitions([
    PDO::class => function () {
        # Obtenemos las variables de entorno necesarias para la conexión a la base de datos
        $host = getenv('DB_HOST') ?: 'db';
        $dbname = getenv('DB_NAME') ?: 'lotr';
        $user = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: 'root';

        try {
            return new PDO(
                "mysql:host={$host};port=3306;dbname={$dbname}",
                $user,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            throw new PDOException("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }
]);

# Creamos el contenedor
$container = $containerBuilder->build();

# Creamos la aplicación con el contenedor
$app = AppFactory::createFromContainer($container);

# Rutas para personajes
$app->post('/characters', CreateCharacterController::class);
$app->get('/characters[/{id}]', ReadCharactersController::class);

# Rutas para facciones
$app->post('/factions', CreateFactionController::class);
$app->get('/factions[/{id}]', ReadFactionsController::class);

# Rutas para equipamientos
$app->post('/equipments', CreateEquipmentController::class);
$app->get('/equipments[/{id}]', ReadEquipmetsController::class);

# Manejamos las rutas no encontradas
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function (Request $request, Response $response) {
    $response->getBody()->write("Ruta no encontrada");
    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
});

$app->run();

