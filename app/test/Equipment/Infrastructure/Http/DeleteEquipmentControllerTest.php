<?php

namespace App\Test\Equipment\Infrastructure\Http;

use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Infrastructure\Persistence\Pdo\Exception\EquipmentNotFoundException;
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

class DeleteEquipmentControllerTest extends TestCase
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
        parent::tearDown();
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
    }


    /**
     * @test
     * @group happy-path
     * @group acceptance
     * @group equipment
     * @group controller
     */
    public function givenARequestToTheControllerWithValidIdWhenDeleteEquipmentThenReturnSuccessMessage()
    {
        // Crear un equipamiento para eliminarlo
        $equipment = new Equipment(
            'Sword of the King',
            'A sword with a hilt of gold and a blade of steel',
            'John Doe'
        );
        $savedEquipment = $this->repository->save($equipment);
        $this->insertedEquipmentIds[] = $savedEquipment->getId();
        $equipmentId = $savedEquipment->getId();    

        // Crear una solicitud para eliminar el equipamiento
        $request = $this->createJsonRequest('DELETE', '/equipments/' . $equipmentId, []);
        
        // Procesar la solicitud
        $response = $this->app->handle($request);

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
     * @group controller
     */
    public function givenARequestToTheControllerWithNonExistentIdWhenDeleteEquipmentThenReturnErrorAsJson() 
    {
        // Crear una solicitud con un ID que no existe
        $request = $this->createJsonRequest('DELETE', '/equipments/999', []);
        
        // Procesar la solicitud
        $response = $this->app->handle($request);

        // Obtener y decodificar la respuesta
        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);
        
        // Verificar la respuesta
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals(EquipmentNotFoundException::MESSAGE, $responseData['error']);
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

