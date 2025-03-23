<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use DI\ContainerBuilder;
use Dotenv\Dotenv;

use App\Character\Domain\CharacterRepository;
use App\Character\Domain\Service\CharacterValidator;
use App\Character\Domain\Exception\CharacterValidationException;
use App\Character\Application\CreateCharacterUseCase;
use App\Character\Application\ValidateCharacterUseCase;
use App\Character\Infrastructure\Http\CreateCharacterController;
use App\Character\Infrastructure\MySQLCharacterRepository;
use App\Character\Infrastructure\Exception\CharacterValidationErrorHandler;
use App\Controller\Character\ReadCharactersController; 

use App\Controller\Equipment\CreateEquipmentController;
use App\Controller\Equipment\ReadEquipmetsController;

use App\Faction\Domain\Exception\FactionValidationException;
use App\Faction\Infrastructure\Exception\FactionValidationErrorHandler;
use App\Faction\Infrastructure\Http\CreateFactionController;
use App\Faction\Infrastructure\MySQLFactionRepository;
use App\Faction\Domain\Service\FactionValidator;
use App\Faction\Application\CreateFactionUseCase;
use App\Faction\Application\ValidateFactionUseCase;
use App\Controller\Faction\ReadFactionsController;
use App\Faction\Domain\FactionRepository;
use Psr\Container\ContainerInterface;


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
    CharacterValidator::class => function () {
        return new CharacterValidator();
    },
    ValidateCharacterUseCase::class => function (ContainerInterface $c) {
        return new ValidateCharacterUseCase(
            $c->get(CharacterValidator::class)
        );
    },
    CreateCharacterUseCase::class => function (ContainerInterface $c) {
        return new CreateCharacterUseCase(
            $c->get(CharacterRepository::class),
            $c->get(ValidateCharacterUseCase::class)
        );
    },
    FactionRepository::class => function (ContainerInterface $c) {
        return new MySQLFactionRepository(
            $c->get(PDO::class)
        );
    },
    FactionValidator::class => function () {
        return new FactionValidator();
    },
    CreateFactionUseCase::class => function (ContainerInterface $c) {
        return new CreateFactionUseCase(
            $c->get(FactionRepository::class),
            $c->get(FactionValidator::class)  
        );
    },
    ValidateFactionUseCase::class => function (ContainerInterface $c) {
        return new ValidateFactionUseCase(
            $c->get(FactionValidator::class)
        );
    },
]);

# Creamos el contenedor
$container = $containerBuilder->build();

# Creamos la aplicación con el contenedor
$app = AppFactory::createFromContainer($container);

# Middleware para capturar las excepciones de validación de personajes.
$app->addErrorMiddleware(true, true, true)
    ->setErrorHandler(CharacterValidationException::class, function (
        Throwable $exception,
    ) use ($app) {
        $handler = new CharacterValidationErrorHandler($app);
        return $handler->handle($exception);
    });

# Middleware para capturar las excepciones de validación de facciones.
$app->addErrorMiddleware(true, true, true)
    ->setErrorHandler(FactionValidationException::class, function (
        Throwable $exception,
    ) use ($app) {
        $handler = new FactionValidationErrorHandler($app);
        return $handler->handle($exception);
    });

# Rutas para personajes
$app->post('/characters', CreateCharacterController::class);
$app->get('/charactersList', ReadCharactersController::class);

# Rutas para facciones
$app->post('/factions', CreateFactionController::class);
$app->get('/factionsList', ReadFactionsController::class);

# Rutas para equipamientos
$app->post('/equipments', CreateEquipmentController::class);
$app->get('/equipments[/{id}]', ReadEquipmetsController::class);

# Manejamos las rutas no encontradas
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function (Response $response) {
    $response->getBody()->write("Ruta no encontrada");
    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
});

$app->run();

