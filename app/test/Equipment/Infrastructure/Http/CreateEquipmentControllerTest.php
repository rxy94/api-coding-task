<?php

namespace App\Test\Equipment\Infrastructure\Http;

use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\EquipmentRepository;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Uri;
use Slim\Psr7\Request as SlimRequest;

class CreateEquipmentControllerTest extends TestCase
{
    /**
     * @test
     * @group happy-path
     * @group integration
     * @group equipment
     * @group controller
     */
    public function givenARequestToTheControllerWithValidDataWhenCreateEquipmentThenReturnTheEquipmentAsJson()
    {
        $app = $this->getAppInstance();

        $equipmentData = [
            'name' => 'Sword of the King',
            'type' => 'A sword with a hilt of gold and a blade of steel',
            'made_by' => 'John Doe',
        ];

        $request = $this->createJsonRequest('POST', '/equipment', $equipmentData);
        $stream = (new StreamFactory())->createStream(json_encode($equipmentData));
        $request = $request->withBody($stream);

        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('equipment', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('El equipamiento se ha creado correctamente', $responseData['message']);

        $repository = $app->getContainer()->get(EquipmentRepository::class);
        $createdEquipment = $repository->findById($responseData['equipment']['id']);

        $this->assertEquals($equipmentData['name'], $createdEquipment->getName());
        $this->assertEquals($equipmentData['type'], $createdEquipment->getType());
        $this->assertEquals($equipmentData['made_by'], $createdEquipment->getMadeBy());
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

    private function createJsonRequest(
        string $method,
        string $path,
        array $data,
        array $headers = ['HTTP_ACCEPT' => 'application/json', 'Content-Type' => 'application/json']
    ): Request {
        $uri = new Uri('', '', 80, $path);
        $handle = fopen('php://temp', 'w+');
        $stream = (new StreamFactory())->createStreamFromResource($handle);
        fwrite($handle, json_encode($data));
        rewind($handle);
    
        $h = new Headers();
        foreach ($headers as $name => $value) {
            $h->addHeader($name, $value);
        }
    
        $request = new SlimRequest($method, $uri, $h, [], [], $stream);
        
        return $request->withParsedBody($data);
    }

    /**
     * @test
     * @group unhappy-path
     * @group integration
     * @group equipment
     * @group controller
     */
    public function givenARequestToTheControllerWithInvalidDataWhenCreateEquipmentThenReturnErrorAsJson()
    {
        $app = $this->getAppInstance();

        $invalidEquipmentData = [
            'name' => '',
            'type' => 'A sword with a hilt of gold and a blade of steel',
            'made_by' => 'John Doe',
        ];

        $request = $this->createJsonRequest('POST', '/equipment', $invalidEquipmentData);
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Campo requerido: name', $responseData['error']);
    }

    /**
     * @test
     * @group unhappy-path
     * @group integration
     * @group equipment
     * @group controller
     */
    public function givenARequestToTheControllerWithMissingFieldsWhenCreateEquipmentThenReturnErrorAsJson()
    {
        $app = $this->getAppInstance();

        $incompleteEquipmentData = [
            'name' => 'Sword of the King',
            'type' => 'A sword with a hilt of gold and a blade of steel',
            'made_by' => '',
        ];

        $request = $this->createJsonRequest('POST', '/equipment', $incompleteEquipmentData);
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Campo requerido: made_by', $responseData['error']);
    }

    /**
     * @test
     * @group unhappy-path
     * @group integration
     * @group equipment
     * @group controller
     */
    public function givenARequestToTheControllerWithValidationExceptionWhenCreateEquipmentThenReturnErrorAsJson()
    {
        $app = $this->getAppInstance();

        $invalidEquipmentData = [
            'name' => str_repeat('a', 101), // Nombre con más de 100 caracteres
            'type' => 'A sword with a hilt of gold and a blade of steel',
            'made_by' => 'John Doe',
        ];

        $request = $this->createJsonRequest('POST', '/equipment', $invalidEquipmentData);
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('El nombre no puede exceder los 100 caracteres', $responseData['error']);
    }
    
}

        
        