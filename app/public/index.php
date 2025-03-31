<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Slim\Factory\AppFactory;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

use App\Character\Domain\CharacterRepository;
use App\Character\Application\CreateCharacterUseCase;
use App\Character\Domain\Service\CharacterValidator;
use App\Character\Infrastructure\Http\CreateCharacterController;
use App\Character\Infrastructure\Http\ReadCharacterByIdController;
use App\Character\Infrastructure\Http\ReadCharacterController;
use App\Character\Infrastructure\Http\DeleteCharacterByIdController;
use App\Character\Infrastructure\Persistence\Pdo\MySQLCharacterRepository;
use App\Character\Infrastructure\Persistence\Cache\CachedMySQLCharacterRepository;

use App\Faction\Domain\FactionRepository;
use App\Faction\Application\CreateFactionUseCase;
use App\Faction\Domain\Service\FactionValidator;
use App\Faction\Infrastructure\Http\ReadFactionController;
use App\Faction\Infrastructure\Http\CreateFactionController;
use App\Faction\Infrastructure\Http\ReadFactionByIdController;
use App\Faction\Infrastructure\Persistence\Pdo\MySQLFactionRepository;
use App\Faction\Infrastructure\Persistence\Cache\CachedMySQLFactionRepository;

use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Domain\Service\EquipmentValidator;
use App\Equipment\Application\CreateEquipmentUseCase;
use App\Equipment\Infrastructure\Http\CreateEquipmentController;
use App\Equipment\Infrastructure\Http\ReadEquipmentByIdController;
use App\Equipment\Infrastructure\Http\ReadEquipmentController;
use App\Equipment\Infrastructure\Persistence\Pdo\MySQLEquipmentRepository;
use App\Equipment\Infrastructure\Persistence\Cache\CachedMySQLEquipmentRepository;

use App\Character\Application\DeleteCharacterUseCase;
use App\Equipment\Infrastructure\Http\DeleteEquipmentByIdController;
use App\Faction\Infrastructure\Http\DeleteFactionByIdController;
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
    Redis::class => function () {
        return new Redis([
            'host' => $_ENV['REDIS_HOST'],
            'port' => (int) $_ENV['REDIS_PORT'],
        ]);
    },
    LoggerInterface::class => function () {
        if ((bool) ((int) $_ENV['DEBUG_MODE'])) {
            $logger = new Logger('app');
            $logger->pushHandler(new StreamHandler('php://stdout', Level::Debug));

            return $logger;
        }

        return new NullLogger();
    },
    # Character
    CharacterRepository::class => function (ContainerInterface $c) {
        if ((bool) ((int) $_ENV['CACHE_ENABLED'])) {
            return new CachedMySQLCharacterRepository(
                new MySQLCharacterRepository($c->get(PDO::class)),
                $c->get(Redis::class),
                $c->get(LoggerInterface::class)
            );
        }

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
    # Faction
    FactionRepository::class => function (ContainerInterface $c) {
        if ((bool) ((int) $_ENV['CACHE_ENABLED'])) {
            return new CachedMySQLFactionRepository(
                new MySQLFactionRepository($c->get(PDO::class)),
                $c->get(Redis::class),
                $c->get(LoggerInterface::class),
            );
        }

        return new MySQLFactionRepository(
            $c->get(PDO::class)
        );
    },
    CreateFactionUseCase::class => function (ContainerInterface $c) {
        return new CreateFactionUseCase(
            $c->get(FactionRepository::class),
            $c->get(FactionValidator::class)
        );
    },
    # Equipment
    EquipmentRepository::class => function (ContainerInterface $c) {
        if ((bool) ((int) $_ENV['CACHE_ENABLED'])) {
            return new CachedMySQLEquipmentRepository(
                new MySQLEquipmentRepository($c->get(PDO::class)),
                $c->get(Redis::class),
                $c->get(LoggerInterface::class),
            );
        }

        return new MySQLEquipmentRepository(
            $c->get(PDO::class)
        );
    },
    CreateEquipmentUseCase::class => function (ContainerInterface $c) {
        return new CreateEquipmentUseCase(
            $c->get(EquipmentRepository::class),
            $c->get(EquipmentValidator::class)
        );
    },
]);

# Creamos el contenedor
$container = $containerBuilder->build();

# Creamos la aplicación con el contenedor
$app = AppFactory::createFromContainer($container);

# Middleware para manejar excepciones
$app->addErrorMiddleware(true, true, true);

# Rutas para personajes
$app->post('/characters', CreateCharacterController::class);
$app->get('/charactersList', ReadCharacterController::class);
$app->get('/characters/{id}', ReadCharacterByIdController::class);
$app->delete('/deleteCharacter/{id}', DeleteCharacterByIdController::class);

# Rutas para facciones
$app->post('/factions', CreateFactionController::class);
$app->get('/factionsList', ReadFactionController::class);
$app->get('/factions/{id}', ReadFactionByIdController::class);
$app->delete('/deleteFaction/{id}', DeleteFactionByIdController::class);

# Rutas para equipamientos
$app->post('/equipments', CreateEquipmentController::class);
$app->get('/equipmentsList', ReadEquipmentController::class);
$app->get('/equipments/{id}', ReadEquipmentByIdController::class);
$app->delete('/deleteEquipment/{id}', DeleteEquipmentByIdController::class);

# Manejamos las rutas no encontradas
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function (Response $response) {
    $response->getBody()->write("Ruta no encontrada");
    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
});

$app->run();

