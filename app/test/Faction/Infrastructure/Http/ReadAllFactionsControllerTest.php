<?php

namespace App\Test\Faction\Infrastructure\Http;

use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionRepository;
use App\Faction\Domain\FactionToArrayTransformer;
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

class ReadAllFactionsControllerTest extends TestCase
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
     * @group read-all-factions
     */
    public function givenARequestToTheControllerWhenReadAllFactionsThenReturnAllFactionsAsJson()
    {
        $app = $this->getAppInstance();

        $repository = $app->getContainer()->get(FactionRepository::class);

        $factions = [
            new Faction(
                'Gondor',
                'El reino de Gondor es uno de los reinos más importantes de la Tierra Media'
            ),
            new Faction(
                'Rohan',
                'El reino de Rohan es conocido por sus jinetes y caballos'
            )
        ];

        $savedFactions = [];
        foreach ($factions as $faction) {
            $savedFaction = $repository->save($faction);
            $savedFactions[] = $savedFaction;
            $this->insertedFactionIds[] = $savedFaction->getId();
        }

        $request = $this->createRequest('GET', '/factions');
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $serializedPayload = json_encode([
            'factions' => array_map(
                function (Faction $faction) {
                    return FactionToArrayTransformer::transform($faction);
                },
                $savedFactions
            ),
            'message' => 'Facciones obtenidas correctamente'
        ]);

        $this->assertEquals($serializedPayload, $payload);
    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group faction
     * @group read-all-factions
     */
    public function givenARequestToTheControllerWhenNoFactionsExistThenReturnEmptyArray()
    {
        $app = $this->getAppInstance();

        $request = $this->createRequest('GET', '/factions');
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $serializedPayload = json_encode([
            'factions' => [],
            'message' => 'Facciones obtenidas correctamente'
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
