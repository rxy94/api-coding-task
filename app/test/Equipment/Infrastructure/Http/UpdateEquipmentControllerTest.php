<?php

namespace App\Test\Equipment\Infrastructure\Http;

use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Domain\Exception\EquipmentNotFoundException;
use App\Equipment\Infrastructure\Http\UpdateEquipmentController;
use PDO;
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

class UpdateEquipmentControllerTest extends TestCase
{
    private PDO $pdo;
    private array $insertedEquipmentIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = $this->createPdoConnection();
    }

    protected function tearDown(): void
    {
        try {
            if (!empty($this->insertedEquipmentIds)) {
                $ids = implode(',', $this->insertedEquipmentIds);
                $this->pdo->exec("DELETE FROM equipments WHERE id IN ($ids)");
            }
        } catch (\Exception $e) {
            error_log("Error al limpiar registros en tearDown: " . $e->getMessage());
        } finally {
            $this->insertedEquipmentIds = [];
        }

        parent::tearDown();
    }

    private function createPdoConnection(): PDO
    {
        return new PDO('mysql:host=db;dbname=test', 'root', 'root');
    }

    /**
     * @test
     * @group happy-path
     * @group acceptance
     * @group equipment
     * @group update-equipment
     */
    public function givenARequestToTheControllerWithValidDataWhenUpdateEquipmentThenReturnTheEquipmentAsJson()
    {
        $app = $this->getAppInstance();
        $repository = $app->getContainer()->get(EquipmentRepository::class);

        // Crear un equipamiento para actualizarlo
        $originalEquipment = new Equipment(
            'Sword of the King',
            'A sword with a hilt of gold and a blade of steel',
            'John Doe'
        );

        $savedEquipment = $repository->save($originalEquipment);
        $this->insertedEquipmentIds[] = $savedEquipment->getId();

        // Datos para actualizar el equipamiento
        $updateData = [
            'name' => 'Sword of the Queen',
            'type' => 'A sword with a hilt of silver and a blade of steel',
            'made_by' => 'Jane Doe'
        ];

        // Crear una solicitud con los datos correctos
        $request = $this->createJsonRequest('PUT', '/equipments/' . $savedEquipment->getId(), $updateData);
        $response = $app->handle($request);

        // Obtener y decodificar la respuesta
        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(UpdateEquipmentController::getSuccessMessage(), $responseData['message']);

        // Verificar que el equipamiento se actualizó correctamente en la base de datos
        $updatedEquipment = $repository->findById($savedEquipment->getId());

        $this->assertEquals($updateData['name'], $updatedEquipment->getName());
        $this->assertEquals($updateData['type'], $updatedEquipment->getType());
        $this->assertEquals($updateData['made_by'], $updatedEquipment->getMadeBy());
    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group equipment
     * @group update-equipment
     */
    public function givenARequestToTheControllerWithInvalidDataWhenUpdateEquipmentThenReturnValidationError()
    {
        $app = $this->getAppInstance();
        $repository = $app->getContainer()->get(EquipmentRepository::class);

        // Crear un equipamiento para actualizarlo
        $originalEquipment = new Equipment(
            'Sword of the King',
            'A sword with a hilt of gold and a blade of steel',
            'John Doe'
        );

        $savedEquipment = $repository->save($originalEquipment);
        $this->insertedEquipmentIds[] = $savedEquipment->getId();

        // Datos inválidos para actualizar el equipamiento
        $updateData = [
            'name' => '', // Nombre vacío
            'type' => 'A sword with a hilt of silver and a blade of steel',
            'made_by' => 'Jane Doe'
        ];

        // Crear una solicitud con los datos inválidos
        $request = $this->createJsonRequest('PUT', '/equipments/' . $savedEquipment->getId(), $updateData);
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        // Verificar la respuesta
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('El nombre es requerido', $responseData['error']);
    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group equipment
     * @group update-equipment
     */
    public function givenARequestToTheControllerWithNonExistentEquipmentWhenUpdateEquipmentThenReturnNotFoundError()
    {
        $app = $this->getAppInstance();

        // Datos para actualizar el equipamiento
        $updateData = [
            'name' => 'Sword of the Queen',
            'type' => 'A sword with a hilt of silver and a blade of steel',
            'made_by' => 'Jane Doe'
        ];

        // Crear una solicitud con un ID que no existe
        $request = $this->createJsonRequest('PUT', '/equipments/999', $updateData);
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        // Verificar la respuesta
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals(EquipmentNotFoundException::build()->getMessage(), $responseData['error']);
    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group equipment
     * @group update-equipment
     */
    public function givenARequestToTheControllerWithMissingDataWhenUpdateEquipmentThenReturnValidationError()
    {
        $app = $this->getAppInstance();
        $repository = $app->getContainer()->get(EquipmentRepository::class);

        // Crear un equipamiento para actualizarlo
        $originalEquipment = new Equipment(
            'Sword of the King',
            'A sword with a hilt of gold and a blade of steel',
            'John Doe'
        );

        $savedEquipment = $repository->save($originalEquipment);
        $this->insertedEquipmentIds[] = $savedEquipment->getId();
        
        // Datos incompletos para actualizar el equipamiento
        $updateData = [
            'name' => 'Sword of the Queen',
            // Falta type
            'made_by' => 'Jane Doe'
        ];

        // Crear una solicitud con datos faltantes
        $request = $this->createJsonRequest('PUT', '/equipments/' . $savedEquipment->getId(), $updateData);
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        // Verificar la respuesta
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals("Campo requerido: type", $responseData['error']);
    }

    private function getAppInstance(): App
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../../../', '.env.test');
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
            