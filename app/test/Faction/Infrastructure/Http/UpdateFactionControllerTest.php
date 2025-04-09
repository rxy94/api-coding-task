<?php

namespace App\Test\Faction\Infrastructure\Http;

use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionRepository;
use App\Faction\Domain\Exception\FactionNotFoundException;
use App\Faction\Domain\Exception\FactionValidationException;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Uri;
use Slim\Psr7\Request as SlimRequest;

class UpdateFactionControllerTest extends TestCase
{
    private PDO $pdo;
    private App $app;
    private FactionRepository $repository;
    private array $insertedFactionIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = $this->createPdoConnection();
        $this->app = $this->getAppInstance();
        $this->repository = $this->app->getContainer()->get(FactionRepository::class);
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
     * @group update-faction
     */
    public function givenARequestToTheControllerWithValidDataWhenUpdateFactionThenReturnTheFactionAsJson()
    {
        $faction = new Faction(
            'Kingdom of Spain',
            'A powerful kingdom in the south of Europe'
        );

        $savedFaction = $this->repository->save($faction);
        $this->insertedFactionIds[] = $savedFaction->getId();

        $updateData = [
            'faction_name' => 'Kingdom of Portugal',
            'description' => 'A powerful kingdom in the north of Europe'
        ];

        $request = $this->createJsonRequest('PUT', '/factions/' . $savedFaction->getId(), $updateData);
        $response = $this->app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('La facción se ha actualizado correctamente', $responseData['message']);

        $updatedFaction = $this->repository->findById($savedFaction->getId());
        $this->assertEquals($updateData['faction_name'], $updatedFaction->getName());
        $this->assertEquals($updateData['description'], $updatedFaction->getDescription());
    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group faction
     * @group update-faction
     */
    public function givenARequestToTheControllerWithInvalidDataWhenUpdateFactionThenReturnErrorAsJson()
    {
        $faction = new Faction(
            'Kingdom of Spain',
            'A powerful kingdom in the south of Europe'
        );

        $savedFaction = $this->repository->save($faction);
        $this->insertedFactionIds[] = $savedFaction->getId();

        $invalidUpdateData = [
            'faction_name' => '', // nombre vacío
            'description' => 'Nueva descripción'
        ];

        $request = $this->createJsonRequest('PUT', '/factions/' . $savedFaction->getId(), $invalidUpdateData);
        $response = $this->app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(FactionValidationException::withFactionNameError()->getMessage(), $responseData['error']);
    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group faction
     * @group update-faction
     */
    public function givenARequestToTheControllerWithNonExistentIdWhenUpdateFactionThenReturnErrorAsJson()
    {
        $updateData = [
            'faction_name' => 'Kingdom of Atlantis',
            'description' => 'A lost kingdom'
        ];

        $request = $this->createJsonRequest('PUT', '/factions/999999', $updateData);
        $response = $this->app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(404, $response->getStatusCode());
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

        return new SlimRequest($method, $uri, $h, [], [], $stream);
    }
}
