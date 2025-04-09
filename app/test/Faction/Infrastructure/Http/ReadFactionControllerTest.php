<?php

namespace App\Test\Faction\Infrastructure\Http;

use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionRepository;
use App\Faction\Domain\FactionToArrayTransformer;
use App\Faction\Domain\Exception\FactionNotFoundException;

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

class ReadFactionControllerTest extends TestCase
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
     * @group read-faction
     */
    public function givenARequestToTheControllerWhenReadFactionThenReturnTheFactionAsJson()
    {
        $app = $this->getAppInstance();
        $repository = $app->getContainer()->get(FactionRepository::class);

        $faction = new Faction(
            'Kingdom of Spain',
            'A powerful kingdom in the south of Europe'
        );
        
        $savedFaction = $repository->save($faction);
        $this->insertedFactionIds[] = $savedFaction->getId();

        $request = $this->createRequest('GET', '/factions/' . $savedFaction->getId());
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('faction', $responseData);
        $this->assertEquals(
            FactionToArrayTransformer::transform($savedFaction),
            $responseData['faction']
        );
        $this->assertEquals('Facción encontrada correctamente', $responseData['message']);
    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group faction
     * @group read-faction
     */
    public function givenARequestToTheControllerWhenFactionNotFoundThenReturnErrorAsJson()
    {
        $app = $this->getAppInstance();

        $nonExistentId = 999999;
        $request = $this->createRequest('GET', '/factions/' . $nonExistentId);
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals(FactionNotFoundException::build()->getMessage(), $responseData['error']);
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