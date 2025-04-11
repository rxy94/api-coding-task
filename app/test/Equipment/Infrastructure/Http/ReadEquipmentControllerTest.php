<?php

namespace App\Test\Equipment\Infrastructure\Http;

use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Domain\EquipmentToArrayTransformer;
use App\Equipment\Domain\Exception\EquipmentNotFoundException;
use App\Equipment\Infrastructure\Http\ReadEquipmentByIdController;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use PDO;
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
     * @group integration
     * @group equipment
     * @group read-equipment
     */
    public function givenARequestToTheControllerWhenReadEquipmentThenReturnTheEquipmentAsJson()
    {
        $app = $this->getAppInstance();
        $repository = $app->getContainer()->get(EquipmentRepository::class);

        $expectedEquipment = new Equipment(
            'Sword of the King',
            'A sword with a hilt of gold and a blade of steel',
            'John Doe',
        );
        
        $savedEquipment = $repository->save($expectedEquipment);
        $this->insertedEquipmentIds[] = $savedEquipment->getId();

        $request = $this->createRequest('GET', '/equipments/' . $savedEquipment->getId());
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('equipment', $responseData);
        $this->assertEquals(
            EquipmentToArrayTransformer::transform($savedEquipment),
            $responseData['equipment']
        );
        $this->assertEquals(ReadEquipmentByIdController::getSuccessMessage(), $responseData['message']);
    }

    /**
     * @test
     * @group unhappy-path  
     * @group acceptance
     * @group equipment
     * @group read-equipment
     */
    public function givenARequestToTheControllerWhenEquipmentNotFoundThenReturnErrorAsJson()
    {
        $app = $this->getAppInstance();

        $nonExistentId = 999999;
        $request = $this->createRequest('GET', '/equipments/' . $nonExistentId);
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(404, $response->getStatusCode());   
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals(EquipmentNotFoundException::build()->getMessage(), $responseData['error']);
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