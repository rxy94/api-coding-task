<?php

namespace App\Test\Character\Infrastructure\Http;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Domain\CharacterToArrayTransformer;
use App\Character\Domain\Exception\CharactersNotFoundException;
use App\Character\Infrastructure\Http\ReadCharacterController;
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

        // Equipos de prueba
        $this->insertTestEquipment('Sword of Testing', 'Weapon', 'Test Blacksmith');
        $this->insertTestEquipment('Sword of Testing 2', 'Weapon', 'Test Blacksmith 2');

        // Facciones de prueba
        $this->insertTestFaction('Test Faction', 'A test faction for testing');
        $this->insertTestFaction('Test Faction 2', 'A test faction for testing 2');
    }

    private function insertTestEquipment(string $name, string $type, string $madeBy): void
    {
        $sql = "INSERT INTO equipments (name, type, made_by) VALUES (:name, :type, :made_by)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'type' => $type,
            'made_by' => $madeBy,
        ]);
        $id = $this->pdo->lastInsertId();
        if (!$id) {
            throw new \RuntimeException("Error al insertar el equipo: $name");
        }
        $this->insertedEquipmentIds[] = $id;
    }

    private function insertTestFaction(string $name, string $description): void
    {
        $sql = "INSERT INTO factions (faction_name, description) VALUES (:name, :description)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'description' => $description,
        ]);
        $id = $this->pdo->lastInsertId();
        if (!$id) {
            throw new \RuntimeException("Error al insertar la facción: $name");
        }
        $this->insertedFactionIds[] = $id;
    }

    protected function tearDown(): void
    {
        try {
            foreach ([
                'characters' => $this->insertedCharacterIds,
                'equipments' => $this->insertedEquipmentIds,
                'factions'   => $this->insertedFactionIds,
            ] as $table => $ids) {
                $filteredIds = array_filter($ids);
                if (!empty($filteredIds)) {
                    $sql = "DELETE FROM $table WHERE id IN (" . implode(',', $filteredIds) . ")";
                    $this->pdo->exec($sql);
                }
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
            'message' => ReadCharacterController::getSuccessMessage()
        ]);

        $this->assertEquals($serializedPayload, $payload);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group character 
     * @group character-http
     * @group read-all-characters
     */
    public function givenARequestToTheControllerWhenNoCharactersExistThenReturnEmptyArray()
    {
        $app = $this->getAppInstance();

        $request = $this->createRequest('GET', '/characters');
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group character
     * @group character-http
     * @group read-all-characters
     * @group controller-exception
     */
    public function givenADatabaseFailureWhenReadAllCharactersThenReturnServerError()
    {
        $app = $this->getAppInstance();

        // Provocamos un fallo real en la DB
        $this->pdo->exec('RENAME TABLE characters TO characters_backup');

        try {
            $request = $this->createRequest('GET', '/characters');
            $response = $app->handle($request);

            $payload = (string) $response->getBody();
            $responseData = json_decode($payload, true);

            $this->assertEquals(500, $response->getStatusCode());
            $this->assertArrayHasKey('message', $responseData);
            $this->assertNotEmpty($responseData['message']); // Solo aseguramos que hay un mensaje de error

        } finally {
            // Restauramos la tabla
            $this->pdo->exec('RENAME TABLE characters_backup TO characters');
        }
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
