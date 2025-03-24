<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;
use DI\ContainerBuilder;
use Dotenv\Dotenv;

use App\Character\Domain\CharacterRepository;
use App\Character\Domain\Exception\CharacterValidationException;
use App\Character\Application\ReadCharacterUseCase;
use App\Character\Application\ReadCharacterByIdUseCase;
use App\Character\Application\CreateCharacterUseCase;
use App\Character\Domain\Service\CharacterValidator;
use App\Character\Infrastructure\MySQLCharacterRepository;
use App\Character\Infrastructure\Http\CreateCharacterController;
use App\Character\Infrastructure\Http\ReadCharacterByIdController;
use App\Character\Infrastructure\Http\ReadCharacterController;

use App\Faction\Domain\FactionRepository;
use App\Faction\Domain\Exception\FactionValidationException;
use App\Faction\Application\ReadFactionUseCase;
use App\Faction\Application\ReadFactionByIdUseCase;
use App\Faction\Application\CreateFactionUseCase;
use App\Faction\Application\ValidateFactionUseCase;
use App\Faction\Infrastructure\MySQLFactionRepository;
use App\Faction\Infrastructure\Http\ReadFactionController;
use App\Faction\Infrastructure\Http\CreateFactionController;
use App\Faction\Infrastructure\Http\ReadFactionByIdController;

use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Application\ReadEquipmentUseCase;
use App\Equipment\Application\CreateEquipmentUseCase;
use App\Equipment\Infrastructure\MySQLEquipmentRepository;
use App\Equipment\Infrastructure\Http\CreateEquipmentController;
use App\Equipment\Infrastructure\Http\ReadEquipmentController;
use App\Shared\Infrastructure\Exception\ValidationErrorHandler;

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
    },
    CharacterRepository::class => function (ContainerInterface $c) {
        return new MySQLCharacterRepository(
            $c->get(PDO::class)
        );
    },
    CreateCharacterUseCase::class => function (ContainerInterface $c) {
        return new CreateCharacterUseCase(
            $c->get(CharacterRepository::class),
            $c->get(CharacterValidator::class)
        );
    },
    ReadCharacterUseCase::class => function (ContainerInterface $c) {
        return new ReadCharacterUseCase(
            $c->get(CharacterRepository::class)
        );
    },
    ReadCharacterByIdUseCase::class => function (ContainerInterface $c) {
        return new ReadCharacterByIdUseCase(
            $c->get(CharacterRepository::class)
        );
    },
    FactionRepository::class => function (ContainerInterface $c) {
        return new MySQLFactionRepository(
            $c->get(PDO::class)
        );
    },
    ReadFactionUseCase::class => function (ContainerInterface $c) {
        return new ReadFactionUseCase(
            $c->get(FactionRepository::class)
        );
    },
    ReadFactionByIdUseCase::class => function (ContainerInterface $c) {
        return new ReadFactionByIdUseCase(
            $c->get(FactionRepository::class)
        );
    },
    CreateFactionUseCase::class => function (ContainerInterface $c) {
        return new CreateFactionUseCase(
            $c->get(FactionRepository::class),
            $c->get(ValidateFactionUseCase::class)  
        );
    },
    EquipmentRepository::class => function (ContainerInterface $c) {
        return new MySQLEquipmentRepository(
            $c->get(PDO::class)
        );
    },
    CreateEquipmentUseCase::class => function (ContainerInterface $c) {
        return new CreateEquipmentUseCase(
            $c->get(EquipmentRepository::class)
        );
    },
    ReadEquipmentUseCase::class => function (ContainerInterface $c) {
        return new ReadEquipmentUseCase(
            $c->get(EquipmentRepository::class)
        );
    },
]);

# Creamos el contenedor
$container = $containerBuilder->build();

# Creamos la aplicación con el contenedor
$app = AppFactory::createFromContainer($container);

# Middleware para capturar las excepciones de validación
$app->addErrorMiddleware(true, true, true)
    ->setErrorHandler([CharacterValidationException::class, FactionValidationException::class], function (
        Throwable $exception,
        Request $request,
        Response $response
    ) use ($app) {
        $handler = new ValidationErrorHandler($app->getResponseFactory());
        return $handler->handle($exception);
    });

# Rutas para personajes
$app->post('/characters', CreateCharacterController::class);
$app->get('/charactersList', ReadCharacterController::class);
$app->get('/characters/{id}', ReadCharacterByIdController::class);

# Rutas para facciones
$app->post('/factions', CreateFactionController::class);
$app->get('/factionsList', ReadFactionController::class);
$app->get('/factions/{id}', ReadFactionByIdController::class);

# Rutas para equipamientos
$app->post('/equipments', CreateEquipmentController::class);
$app->get('/equipmentsList', ReadEquipmentController::class);

# Manejamos las rutas no encontradas
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function (Response $response) {
    $response->getBody()->write("Ruta no encontrada");
    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
});

$app->run();

