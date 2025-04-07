<?php

namespace App\Test\Equipment\Infrastructure\Http;

use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Domain\EquipmentToArrayTransformer;

use DI\ContainerBuilder;
use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Uri;
use Slim\Psr7\Headers;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Request as SlimRequest;
use Psr\Http\Message\ServerRequestInterface as Request;


class ReadEquipmentControllerTest extends TestCase
{
    /**
     * @test
     * @group happy-path
     * @group integration
     * @group equipment
     * @group controller
     */
    public function givenARequestToTheControllerWithOneEquipmentIdWhenReadEquipmentThenReturnTheEquipmentAsJson()
    {
        $app = $this->getAppInstance();

        $repository = $app->getContainer()->get(EquipmentRepository::class);

        $expectedEquipment = new Equipment(
            name: 'Sword of the King',
            type: 'A sword with a hilt of gold and a blade of steel',
            made_by: 'John Doe',
        );
        
        $savedEquipment = $repository->save($expectedEquipment);

        $request = $this->createRequest('GET', '/equipments/' . $savedEquipment->getId());
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $serializedPayload = json_encode([
            'equipment' => EquipmentToArrayTransformer::transform($savedEquipment),
            'message' => 'Equipamiento encontrado correctamente'
        ]);

        $this->assertEquals($serializedPayload, $payload);
    }

    private function getAppInstance(): App
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../../../');
        $dotenv->load();

        $containerBuilder = new ContainerBuilder();

        $settings = require __DIR__ . '/../../../../config/definitions.php';
        $settings($containerBuilder);

        $container = $containerBuilder->build();

        AppFactory::setContainer($container);
        $app = AppFactory::create();

        $routes = require __DIR__ . '/../../../../config/routes.php';
        $routes($app);

        return $app;
    }

    private function createRequest(
        string $method,
        string $path,
        array $headers = ['HTTP_ACCEPT' => 'application/json'],
        array $cookies = [],
        array $serverParams = []
    ): Request {
        $uri = new Uri('', '', 80, $path);
        $handle = fopen('php://temp', 'w+');
        $stream = (new StreamFactory())->createStreamFromResource($handle);

        $h = new Headers();
        foreach ($headers as $name => $value) {
            $h->addHeader($name, $value);
        }

        return new SlimRequest($method, $uri, $h, $cookies, $serverParams, $stream);
    }
    
}