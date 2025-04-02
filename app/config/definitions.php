<?php

use App\Character\Application\CreateCharacterUseCase;
use App\Character\Application\UpdateCharacterUseCase;
use App\Character\Domain\CharacterRepository;
use App\Character\Domain\Service\CharacterValidator;
use App\Character\Infrastructure\Persistence\Cache\CachedMySQLCharacterRepository;
use App\Character\Infrastructure\Persistence\Pdo\MySQLCharacterRepository;

use App\Equipment\Application\CreateEquipmentUseCase;
use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Domain\Service\EquipmentValidator;
use App\Equipment\Infrastructure\Persistence\Cache\CachedMySQLEquipmentRepository;
use App\Equipment\Infrastructure\Persistence\Pdo\MySQLEquipmentRepository;

use App\Faction\Application\CreateFactionUseCase;
use App\Faction\Domain\FactionRepository;
use App\Faction\Domain\Service\FactionValidator;
use App\Faction\Infrastructure\Persistence\Cache\CachedMySQLFactionRepository;
use App\Faction\Infrastructure\Persistence\Pdo\MySQLFactionRepository;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

use App\Character\Application\ReadCharacterByIdUseCase;

# Definimos las dependencias del contenedor
return function (ContainerBuilder $containerBuilder) {

    $containerBuilder->addDefinitions([
        # Conexión a la base de datos
        PDO::class => function () {
            $conn = new PDO(
                'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'],
                $_ENV['DB_USER'],
                $_ENV['DB_PASSWORD'],
            );

            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            return $conn;
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
        ReadCharacterByIdUseCase::class => function (ContainerInterface $c) {
            return new ReadCharacterByIdUseCase(
                $c->get(CharacterRepository::class),
                $c->get(CharacterValidator::class)
            );
        },
        UpdateCharacterUseCase::class => function (ContainerInterface $c) {
            return new UpdateCharacterUseCase(
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

};
