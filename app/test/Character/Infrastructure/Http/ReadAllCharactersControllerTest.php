<?php

namespace App\Test\Character\Infrastructure\Http;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Domain\CharacterToArrayTransformer;
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

class ReadAllCharactersControllerTest extends TestCase
{
    private PDO $pdo;
    private array $insertedCharacterIds = [];
    private array $insertedEquipmentIds = [];
    private array $insertedFactionIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = $this->createPdoConnection();
        
        // Crear equipos de prueba
        $this->pdo->exec("INSERT INTO equipments (name, type, made_by) VALUES ('Sword of Testing', 'Weapon', 'Test Blacksmith')");
        $this->insertedEquipmentIds[] = $this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO equipments (name, type, made_by) VALUES ('Sword of Testing 2', 'Weapon', 'Test Blacksmith 2')");
        $this->insertedEquipmentIds[] = $this->pdo->lastInsertId();

        // Crear facciones de prueba
        $this->pdo->exec("INSERT INTO factions (faction_name, description) VALUES ('Test Faction', 'A test faction for testing')");
        $this->insertedFactionIds[] = $this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO factions (faction_name, description) VALUES ('Test Faction 2', 'A test faction for testing 2')");
        $this->insertedFactionIds[] = $this->pdo->lastInsertId();
    }

    protected function tearDown(): void
    {
        try {
            // Eliminar personajes
            if (!empty($this->insertedCharacterIds)) {
                $ids = implode(',', $this->insertedCharacterIds);
                $this->pdo->exec("DELETE FROM characters WHERE id IN ($ids)");
            }

            // Eliminar equipos
            if (!empty($this->insertedEquipmentIds)) {
                $ids = implode(',', $this->insertedEquipmentIds);
                $this->pdo->exec("DELETE FROM equipments WHERE id IN ($ids)");
            }

            // Eliminar facciones
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
     * @group read-all-characters
     */
    public function givenARequestToTheControllerWhenReadAllCharactersThenReturnAllCharactersAsJson()
    {
        $app = $this->getAppInstance();

        $repository = $app->getContainer()->get(CharacterRepository::class);

        $characters = [
            new Character(
                'John Doe',
                '1990-01-01',
                'Kingdom of Spain',
                $this->insertedEquipmentIds[0],
                $this->insertedFactionIds[0]
            ),
            new Character(
                'Jane Smith',
                '1992-05-15',
                'Kingdom of France',
                $this->insertedEquipmentIds[1],
                $this->insertedFactionIds[1]
            )
        ];

        $savedCharacters = [];
        foreach ($characters as $character) {
            $savedCharacter = $repository->save($character);
            $savedCharacters[] = $savedCharacter;
            $this->insertedCharacterIds[] = $savedCharacter->getId();
        }

        $request = $this->createRequest('GET', '/characters');
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $serializedPayload = json_encode([
            'characters' => array_map(
                function (Character $character) {
                    return CharacterToArrayTransformer::transform($character);
                },
                $savedCharacters
            ),
            'message' => 'Personajes obtenidos correctamente'
        ]);

        $this->assertEquals($serializedPayload, $payload);
    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group character 
     * @group read-all-characters
     */
    public function givenARequestToTheControllerWhenNoCharactersExistThenReturnEmptyArray()
    {
        $app = $this->getAppInstance();

        $request = $this->createRequest('GET', '/characters');
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $serializedPayload = json_encode([
            'characters' => [],
            'message' => 'Personajes obtenidos correctamente'
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
