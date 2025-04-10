<?php

namespace App\Test\Character\Infrastructure\Http;

use App\Character\Domain\CharacterRepository;
use App\Character\Domain\Exception\CharacterValidationException;
use App\Character\Infrastructure\Http\CreateCharacterController;
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

class CreateCharacterControllerTest extends TestCase
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
     * @group create-character
     */
    public function givenARequestToTheControllerWithValidDataWhenCreateCharacterThenReturnTheCharacterAsJson()
    {
        $app = $this->getAppInstance();

        $characterData = [
            'name' => 'John Doe',
            'birth_date' => '1990-01-01',
            'kingdom' => 'Kingdom of Spain',
            'equipment_id' => $this->insertedEquipmentIds[0],
            'faction_id' => $this->insertedFactionIds[0]
        ];

        $request = $this->createJsonRequest('POST', '/characters', $characterData);
        $requestBody = json_encode($characterData);
        $stream = (new StreamFactory())->createStream($requestBody);
        $request = $request->withBody($stream);

        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        echo "Status Code: " . $response->getStatusCode() . "\n";
        echo "Response Body: " . $payload . "\n";

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('character', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals(CreateCharacterController::getSuccessMessage(), $responseData['message']);

        $repository = $app->getContainer()->get(CharacterRepository::class);
        $createdCharacter = $repository->findById($responseData['character']['id']);
        $this->insertedCharacterIds[] = $createdCharacter->getId();

        $this->assertEquals($characterData['name'], $createdCharacter->getName());
        $this->assertEquals($characterData['birth_date'], $createdCharacter->getBirthDate());
        $this->assertEquals($characterData['kingdom'], $createdCharacter->getKingdom());
        $this->assertEquals($characterData['equipment_id'], $createdCharacter->getEquipmentId());
        $this->assertEquals($characterData['faction_id'], $createdCharacter->getFactionId());
    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group character 
     * @group character-http
     * @group create-character
     */
    public function givenARequestToTheControllerWithInvalidDataWhenCreateCharacterThenReturnErrorAsJson()
    {
        $app = $this->getAppInstance();

        $invalidCharacterData = [
            'name' => '',
            'birth_date' => '1990-01-01',
            'kingdom' => 'Kingdom of Spain',
            'equipment_id' => $this->insertedEquipmentIds[0],
            'faction_id' => $this->insertedFactionIds[0]
        ];

        $request = $this->createJsonRequest('POST', '/characters', $invalidCharacterData);
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Campo requerido: name', $responseData['error']);
    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group character     
     * @group character-http
     * @group create-character
     */
    public function givenARequestToTheControllerWithMissingFieldsWhenCreateCharacterThenReturnErrorAsJson()
    {
        $app = $this->getAppInstance();

        $incompleteCharacterData = [
            'name' => 'John Doe',
            'birth_date' => '1990-01-01',
            // Falta kingdom
            'equipment_id' => $this->insertedEquipmentIds[0],
            'faction_id' => $this->insertedFactionIds[0]
        ];

        $request = $this->createJsonRequest('POST', '/characters', $incompleteCharacterData);
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Campo requerido: kingdom', $responseData['error']);
    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group character     
     * @group character-http
     * @group create-character
     */
    public function givenARequestToTheControllerWithValidationExceptionWhenCreateCharacterThenReturnErrorAsJson()
    {
        $app = $this->getAppInstance();

        $invalidCharacterData = [
            'name' => str_repeat('a', 101),
            'birth_date' => '1990-01-01',
            'kingdom' => 'Kingdom of Spain',
            'equipment_id' => $this->insertedEquipmentIds[0],
            'faction_id' => $this->insertedFactionIds[0]
        ];

        $request = $this->createJsonRequest('POST', '/characters', $invalidCharacterData);
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        echo "Status Code: " . $response->getStatusCode() . "\n";
        echo "Response Body: " . $payload . "\n";

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertArrayHasKey('error', $responseData);
        $expectedMessage = CharacterValidationException::withNameLengthError()->getMessage();
        $this->assertEquals($expectedMessage, $responseData['error']);
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
