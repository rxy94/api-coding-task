<?php

use Slim\Factory\AppFactory;
use DI\ContainerBuilder;
use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

# Cargamos las variables de entorno
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

# Creamos el contenedor de dependencias
$containerBuilder = new ContainerBuilder();

# Cargamos las definiciones de las dependencias
$definitions = require __DIR__ . '/../config/definitions.php';
$definitions($containerBuilder);

# Creamos el contenedor
$container = $containerBuilder->build();
# Creamos la aplicación con el contenedor
$app = AppFactory::createFromContainer($container);

# Middleware para manejar excepciones
$app->addErrorMiddleware(true, true, true);

# Middleware para parsear el cuerpo de la petición
$app->addBodyParsingMiddleware();
//$app->add(new BodyParsingMiddleware());

$routes = require __DIR__ . '/../config/routes.php';
$routes($app);

$app->run();

