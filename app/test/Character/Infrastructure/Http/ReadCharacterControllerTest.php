<?php

namespace App\Test\Character\Infrastructure\Http;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Domain\CharacterToArrayTransformer;
use App\Character\Domain\Exception\CharacterNotFoundException;
use App\Character\Infrastructure\Http\ReadCharacterByIdController;
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

class ReadCharacterControllerTest extends TestCase
{
    private PDO $pdo;
    private array $insertedCharacterIds = [];
    private array $insertedEquipmentIds = [];
    private array $insertedFactionIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = $this->createPdoConnection();

        // Insertar equipos de prueba
        $this->pdo->exec("INSERT INTO equipments (name, type, made_by) VALUES ('Sword of Testing', 'Weapon', 'Test Blacksmith')");
        $this->insertedEquipmentIds[] = $this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO equipments (name, type, made_by) VALUES ('Shield of Testing', 'Shield', 'Test Smith')");
        $this->insertedEquipmentIds[] = $this->pdo->lastInsertId();

        // Insertar facciones de prueba
        $this->pdo->exec("INSERT INTO factions (faction_name, description) VALUES ('Test Faction', 'Testing only')");
        $this->insertedFactionIds[] = $this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO factions (faction_name, description) VALUES ('Another Faction', 'Also for tests')");
        $this->insertedFactionIds[] = $this->pdo->lastInsertId();
    }

    protected function tearDown(): void
    {
        try {
            if (!empty($this->insertedCharacterIds)) {
                $ids = implode(',', $this->insertedCharacterIds);
                $this->pdo->exec("DELETE FROM characters WHERE id IN ($ids)");
            }

            if (!empty($this->insertedEquipmentIds)) {
                $ids = implode(',', $this->insertedEquipmentIds);
                $this->pdo->exec("DELETE FROM equipments WHERE id IN ($ids)");
            }

            if (!empty($this->insertedFactionIds)) {
                $ids = implode(',', $this->insertedFactionIds);
                $this->pdo->exec("DELETE FROM factions WHERE id IN ($ids)");
            }
            
        } catch (\Exception $e) {
            error_log("Error al limpiar registros en tearDown: " . $e->getMessage());

        } finally {
            $this->insertedCharacterIds = [];
            $this->insertedEquipmentIds = [];
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
     * @group character
     * @group character-http
     * @group read-character
     */
    public function givenARequestToTheControllerWhenReadCharacterThenReturnTheCharacterAsJson()
    {
        $app = $this->getAppInstance();
        $repository = $app->getContainer()->get(CharacterRepository::class);

        $character = new Character(
            'John Doe',
            '1990-01-01',
            'Kingdom of Spain',
            $this->insertedEquipmentIds[0],
            $this->insertedFactionIds[0]
        );

        $savedCharacter = $repository->save($character);
        $this->insertedCharacterIds[] = $savedCharacter->getId();

        $request = $this->createRequest('GET', '/characters/' . $savedCharacter->getId());
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('character', $responseData);
        $this->assertEquals(
            CharacterToArrayTransformer::transform($savedCharacter),
            $responseData['character']
        );
        $this->assertEquals(ReadCharacterByIdController::getSuccessMessage(), $responseData['message']);
    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group character
     * @group character-http
     * @group read-character
     */
    public function givenARequestToTheControllerWhenCharacterNotFoundThenReturnErrorAsJson()
    {
        $app = $this->getAppInstance();

        // Se usa un ID muy alto para asegurar que no existe
        $nonExistentId = 999999;
        $request = $this->createRequest('GET', '/characters/' . $nonExistentId);
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        echo "Status Code: " . $response->getStatusCode() . "\n";
        echo "Response Body: " . $payload . "\n";

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals(CharacterNotFoundException::build()->getMessage(), $responseData['error']);
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
