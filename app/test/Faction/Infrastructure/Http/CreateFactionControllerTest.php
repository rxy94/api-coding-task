<?php

namespace App\Test\Faction\Infrastructure\Http;

use App\Faction\Domain\FactionRepository;
use App\Faction\Domain\Exception\FactionValidationException;
use PDO;

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

class CreateFactionControllerTest extends TestCase
{
    private PDO $pdo;
    private array $insertedFactionIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = $this->createPdoConnection();
    }

    protected function tearDown(): void
    {
        try {
            if (!empty($this->insertedFactionIds)) {
                $ids = implode(',', $this->insertedFactionIds);
                $this->pdo->exec("DELETE FROM factions WHERE id IN ($ids)");
            }
        } catch (\Exception $e) {
            error_log("Error al limpiar registros en tearDown: " . $e->getMessage());
        } finally {
            $this->insertedFactionIds = [];
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
     * @group faction
     * @group create-faction
     */
    public function givenARequestToTheControllerWithValidDataWhenCreateFactionThenReturnTheFactionAsJson()
    {
        $app = $this->getAppInstance();

        $factionData = [
            'faction_name' => 'Kingdom of Spain',
            'description' => 'A powerful kingdom in the south of Europe'
        ];

        $request = $this->createJsonRequest('POST', '/factions', $factionData);
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('faction', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('La facción se ha creado correctamente', $responseData['message']);

        $repository = $app->getContainer()->get(FactionRepository::class);
        $createdFaction = $repository->findById($responseData['faction']['id']);
        $this->insertedFactionIds[] = $createdFaction->getId();

        $this->assertEquals($factionData['faction_name'], $createdFaction->getName());
        $this->assertEquals($factionData['description'], $createdFaction->getDescription());
    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group faction
     * @group create-faction
     */
    public function givenARequestToTheControllerWithInvalidDataWhenCreateFactionThenReturnErrorAsJson()
    {
        $app = $this->getAppInstance();

        $invalidFactionData = [
            'faction_name' => '',
            'description' => 'A powerful kingdom in the south of Europe'
        ];

        $request = $this->createJsonRequest('POST', '/factions', $invalidFactionData);
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Campo requerido: faction_name', $responseData['error']);
    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group faction
     * @group create-faction
     */
    public function givenARequestToTheControllerWithMissingFieldsWhenCreateFactionThenReturnErrorAsJson()
    {
        $app = $this->getAppInstance();

        $incompleteFactionData = [
            'faction_name' => 'Kingdom of Spain'
            // Falta description
        ];

        $request = $this->createJsonRequest('POST', '/factions', $incompleteFactionData);
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Campo requerido: description', $responseData['error']);
    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group faction
     * @group create-faction
     */
    public function givenARequestToTheControllerWithValidationExceptionWhenCreateFactionThenReturnErrorAsJson()
    {
        $app = $this->getAppInstance();

        $invalidFactionData = [
            'faction_name' => str_repeat('a', 101), // Nombre con más de 100 caracteres
            'description' => 'A powerful kingdom in the south of Europe'
        ];

        $request = $this->createJsonRequest('POST', '/factions', $invalidFactionData);
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals(FactionValidationException::withFactionNameLengthError()->getMessage(), $responseData['error']);
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