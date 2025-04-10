<?php

namespace App\Test\Equipment\Infrastructure\Http;

use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Domain\EquipmentToArrayTransformer;
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

class ReadAllEquipmentsControllerTest extends TestCase
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
     * @group acceptance
     * @group equipment
     * @group read-all-equipments
     */
    public function givenARequestToTheControllerWhenReadAllEquipmentsThenReturnAllEquipmentsAsJson()
    {
        $app = $this->getAppInstance();
        $repository = $app->getContainer()->get(EquipmentRepository::class);

        $equipments = [
            new Equipment(
                'Sword',
                'Weapon',
                'John Doe'
            ),
            new Equipment(
                'Shield',
                'Armor',
                'Jane Smith'
            ),
            new Equipment(
                'Bow',
                'Weapon',
                'Jane Smith'
            ),
        ];

        $savedEquipments = [];
        foreach ($equipments as $equipment) {
            $savedEquipment = $repository->save($equipment);
            $savedEquipments[] = $savedEquipment;
            $this->insertedEquipmentIds[] = $savedEquipment->getId();
        }

        $request = $this->createRequest('GET', '/equipments');
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $serializedPayload = json_encode([
            'equipments' => array_map(
                function (Equipment $equipment) {
                    return EquipmentToArrayTransformer::transform($equipment);
                },
                $savedEquipments
            ),
            'message' => 'Equipos obtenidos correctamente'
        ]);

        $this->assertEquals($serializedPayload, $payload);
    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group equipment
     * @group read-all-equipments
    */
    public function givenARequestToTheControllerWhenNoEquipmentsExistThenReturnEmptyArray()
    {
        $app = $this->getAppInstance();

        $request = $this->createRequest('GET', '/equipments');
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $serializedPayload = json_encode([
            'equipments' => [],
            'message' => 'Equipos obtenidos correctamente'
        ]);

        $this->assertEquals($serializedPayload, $payload);
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