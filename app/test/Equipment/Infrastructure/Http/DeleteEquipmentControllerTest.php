<?php

namespace App\Test\Equipment\Infrastructure\Http;

use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Domain\Exception\EquipmentNotFoundException;
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

class DeleteEquipmentControllerTest extends TestCase
{
    private PDO $pdo;
    private array $insertedEquipmentIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = $this->createPdoConnection();
        $this->insertedEquipmentIds[] = $this->pdo->lastInsertId();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
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
     * @group delete-equipment
     */
    public function givenARequestToTheControllerWithValidIdWhenDeleteEquipmentThenReturnSuccessMessage()
    {
        $app = $this->getAppInstance();
        $repository = $app->getContainer()->get(EquipmentRepository::class);

        // Crear un equipamiento para eliminarlo
        $equipment = new Equipment(
            'Sword of the King',
            'A sword with a hilt of gold and a blade of steel',
            'John Doe'
        );

        $savedEquipment = $repository->save($equipment);
        $this->insertedEquipmentIds[] = $savedEquipment->getId();  

        // Crear una solicitud para eliminar el equipamiento
        $request = $this->createJsonRequest('DELETE', '/equipments/' . $savedEquipment->getId(), []);
        
        // Procesar la solicitud
        $response = $app->handle($request);

        // Obtener y decodificar la respuesta
        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);
        
        // Verificar la respuesta
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Equipamiento eliminado correctamente', $responseData['message']);
    }

    /**
     * @test    
     * @group unhappy-path
     * @group acceptance
     * @group equipment
     * @group delete-equipment
     */
    public function givenARequestToTheControllerWithNonExistentIdWhenDeleteEquipmentThenReturnErrorAsJson() 
    {
        $app = $this->getAppInstance();

        // Crear una solicitud con un ID que no existe
        $request = $this->createJsonRequest('DELETE', '/equipments/999', []);
        
        // Procesar la solicitud
        $response = $app->handle($request);

        // Obtener y decodificar la respuesta
        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);
        
        // Verificar la respuesta
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals(EquipmentNotFoundException::build()->getMessage(), $responseData['error']);
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

