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

class UpdateEquipmentControllerTest extends TestCase
{
    private App $app;
    private EquipmentRepository $repository;
    private array $insertedEquipmentIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = $this->getAppInstance();
        $this->repository = $this->app->getContainer()->get(EquipmentRepository::class);
    }

    protected function tearDown(): void
    {
        try {
            // Eliminar solo los registros que hemos insertado en este test
            if (!empty($this->insertedEquipmentIds)) {
                $pdo = $this->app->getContainer()->get(\PDO::class);
                $ids = implode(',', $this->insertedEquipmentIds);
                $pdo->exec("DELETE FROM equipments WHERE id IN ($ids)");
            }
        } catch (\Exception $e) {
            // Si hay algún error al limpiar, lo registramos pero no lo propagamos
            // para no enmascarar el error original del test
            error_log("Error al limpiar registros en tearDown: " . $e->getMessage());
        } finally {
            // Limpiar los arrays para el siguiente test
            $this->insertedEquipmentIds = [];
        }

        parent::tearDown();
    }

    /**
     * @test
     * @group happy-path
     * @group acceptance
     * @group equipment
     * @group controller
     */
    public function givenARequestToTheControllerWithValidDataWhenUpdateEquipmentThenReturnTheEquipmentAsJson()
    {
        // Crear un equipamiento para actualizarlo
        $originalEquipment = new Equipment(
            'Sword of the King',
            'A sword with a hilt of gold and a blade of steel',
            'John Doe'
        );

        $savedEquipment = $this->repository->save($originalEquipment);
        $this->insertedEquipmentIds[] = $savedEquipment->getId();
        $equipmentId = $savedEquipment->getId();

        // Datos para actualizar el equipamiento
        $updateData = [
            'name' => 'Sword of the Queen',
            'type' => 'A sword with a hilt of silver and a blade of steel',
            'made_by' => 'Jane Doe'
        ];

        // Crear una solicitud con los datos correctos
        $request = $this->createJsonRequest('PUT', '/equipment/' . $equipmentId, $updateData);
        
        // Asegurarse de que el cuerpo de la solicitud se establece correctamente
        $requestBody = json_encode($updateData);
        $stream = (new StreamFactory())->createStream($requestBody);
        $request = $request->withBody($stream);
        
        // Procesar la solicitud
        $response = $this->app->handle($request);

        // Obtener y decodificar la respuesta
        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);
        
        // Depuración
        echo "Status Code: " . $response->getStatusCode() . "\n";
        echo "Response Body: " . $payload . "\n";

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('equipment', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('El equipamiento se ha actualizado correctamente', $responseData['message']);

        // Verificar que el equipamiento se actualizó correctamente en la base de datos
        $updatedEquipment = $this->repository->findById($equipmentId);

        $this->assertEquals($updateData['name'], $updatedEquipment->getName());
        $this->assertEquals($updateData['type'], $updatedEquipment->getType());
        $this->assertEquals($updateData['made_by'], $updatedEquipment->getMadeBy());
    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group equipment
     * @group controller
     */
    public function givenARequestToTheControllerWithInvalidDataWhenUpdateEquipmentThenReturnValidationError()
    {
        // Crear un equipamiento para actualizarlo
        $originalEquipment = new Equipment(
            'Sword of the King',
            'A sword with a hilt of gold and a blade of steel',
            'John Doe'
        );

        $savedEquipment = $this->repository->save($originalEquipment);
        $this->insertedEquipmentIds[] = $savedEquipment->getId();
        $equipmentId = $savedEquipment->getId();

        // Datos inválidos para actualizar el equipamiento
        $updateData = [
            'name' => '', // Nombre vacío
            'type' => 'A sword with a hilt of silver and a blade of steel',
            'made_by' => 'Jane Doe'
        ];

        // Crear una solicitud con los datos inválidos
        $request = $this->createJsonRequest('PUT', '/equipment/' . $equipmentId, $updateData);
        $requestBody = json_encode($updateData);
        $stream = (new StreamFactory())->createStream($requestBody);
        $request = $request->withBody($stream);
        
        // Procesar la solicitud
        $response = $this->app->handle($request);

        // Verificar la respuesta
        $this->assertEquals(400, $response->getStatusCode());
        $responseData = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('error', $responseData);
    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group equipment
     * @group controller
     */
    public function givenARequestToTheControllerWithNonExistentEquipmentWhenUpdateEquipmentThenReturnNotFoundError()
    {
        // Datos para actualizar el equipamiento
        $updateData = [
            'name' => 'Sword of the Queen',
            'type' => 'A sword with a hilt of silver and a blade of steel',
            'made_by' => 'Jane Doe'
        ];

        // Crear una solicitud con un ID que no existe
        $request = $this->createJsonRequest('PUT', '/equipment/999', $updateData);
        $requestBody = json_encode($updateData);
        $stream = (new StreamFactory())->createStream($requestBody);
        $request = $request->withBody($stream);
        
        // Procesar la solicitud
        $response = $this->app->handle($request);

        // Verificar la respuesta
        $this->assertEquals(404, $response->getStatusCode());
        $responseData = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('error', $responseData);
    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group equipment
     * @group controller
     */
    public function givenARequestToTheControllerWithMissingDataWhenUpdateEquipmentThenReturnValidationError()
    {
        // Crear un equipamiento para actualizarlo
        $originalEquipment = new Equipment(
            'Sword of the King',
            'A sword with a hilt of gold and a blade of steel',
            'John Doe'
        );

        $savedEquipment = $this->repository->save($originalEquipment);
        $this->insertedEquipmentIds[] = $savedEquipment->getId();
        $equipmentId = $savedEquipment->getId();

        // Datos incompletos para actualizar el equipamiento
        $updateData = [
            'name' => 'Sword of the Queen',
            // Falta type
            'made_by' => 'Jane Doe'
        ];

        // Crear una solicitud con datos faltantes
        $request = $this->createJsonRequest('PUT', '/equipment/' . $equipmentId, $updateData);
        $requestBody = json_encode($updateData);
        $stream = (new StreamFactory())->createStream($requestBody);
        $request = $request->withBody($stream);
        
        // Procesar la solicitud
        $response = $this->app->handle($request);

        // Verificar la respuesta
        $this->assertEquals(400, $response->getStatusCode());
        $responseData = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('error', $responseData);
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
}
            