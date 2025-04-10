<?php

namespace App\Test\Character\Infrastructure\Http;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Infrastructure\Http\UpdateCharacterController;
use App\Character\Domain\Exception\CharacterNotFoundException;
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

class UpdateCharacterControllerTest extends TestCase
{
    private PDO $pdo;
    private array $insertedCharacterIds = [];
    private array $insertedEquipmentIds = [];
    private array $insertedFactionIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = $this->createPdoConnection();

        $this->insertTestEquipment('Sword of Testing', 'Weapon', 'Test Blacksmith');
        $this->insertTestEquipment('Sword of Testing 2', 'Weapon', 'Test Blacksmith 2');

        $this->insertTestFaction('Test Faction', 'A test faction for testing');
        $this->insertTestFaction('Test Faction 2', 'A test faction for testing 2');
    }

    private function insertTestEquipment(string $name, string $type, string $madeBy): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO equipments (name, type, made_by) VALUES (:name, :type, :made_by)");
        $stmt->execute(['name' => $name, 'type' => $type, 'made_by' => $madeBy]);
        $id = $this->pdo->lastInsertId();
        if (!$id) {
            throw new \RuntimeException("Error al insertar el equipo: $name");
        }
        $this->insertedEquipmentIds[] = $id;
    }

    private function insertTestFaction(string $name, string $description): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO factions (faction_name, description) VALUES (:name, :description)");
        $stmt->execute(['name' => $name, 'description' => $description]);
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
     * @group update-character
     */
    public function givenARequestToTheControllerWithValidDataWhenUpdateCharacterThenReturnTheUpdatedCharacterAsJson()
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

        $updateData = [
            'name' => 'John Updated',
            'birth_date' => '1995-05-05',
            'kingdom' => 'Kingdom of Portugal',
            'equipment_id' => $this->insertedEquipmentIds[0],
            'faction_id' => $this->insertedFactionIds[0]
        ];

        $request = $this->createJsonRequest('PUT', '/characters/' . $savedCharacter->getId(), $updateData);
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(UpdateCharacterController::getSuccessMessage(), $responseData['message']);

        $updatedCharacter = $repository->findById($savedCharacter->getId());

        $this->assertEquals($updateData['name'], $updatedCharacter->getName());
        $this->assertEquals($updateData['birth_date'], $updatedCharacter->getBirthDate());
        $this->assertEquals($updateData['kingdom'], $updatedCharacter->getKingdom());
        $this->assertEquals($updateData['equipment_id'], $updatedCharacter->getEquipmentId());
        $this->assertEquals($updateData['faction_id'], $updatedCharacter->getFactionId());
    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group character
     * @group character-http
     * @group update-character
     */
    public function givenARequestToTheControllerWithInvalidDataWhenUpdateCharacterThenReturnErrorAsJson()
    {
        $app = $this->getAppInstance();

        $repository = $app->getContainer()->get(CharacterRepository::class);

        // Crear un personaje inicial
        $character = new Character(
            'John Doe',
            '1990-01-01',
            'Kingdom of Spain',
            $this->insertedEquipmentIds[0],
            $this->insertedFactionIds[0]
        );

        $savedCharacter = $repository->save($character);
        $this->insertedCharacterIds[] = $savedCharacter->getId();

        // Datos inválidos para actualizar
        $invalidUpdateData = [
            'name' => '', // Nombre vacío (inválido)
            'birth_date' => '1995-05-05',
            'kingdom' => 'Kingdom of Portugal',
            'equipment_id' => $this->insertedEquipmentIds[0],
            'faction_id' => $this->insertedFactionIds[0]
        ];

        $request = $this->createJsonRequest('PUT', '/characters/' . $savedCharacter->getId(), $invalidUpdateData);
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Campo requerido: name', $responseData['error']);
    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group character
     * @group character-http
     * @group update-character
     */
    public function givenARequestToTheControllerWithNonExistentIdWhenUpdateCharacterThenReturnErrorAsJson()
    {
        $app = $this->getAppInstance();

        $updateData = [
            'name' => 'John Updated',
            'birth_date' => '1995-05-05',
            'kingdom' => 'Kingdom of Portugal',
            'equipment_id' => $this->insertedEquipmentIds[0],
            'faction_id' => $this->insertedFactionIds[0]
        ];

        $request = $this->createJsonRequest('PUT', '/characters/999', $updateData);
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(404, $response->getStatusCode());
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

    private function createJsonRequest(
        string $method,
        string $path,
        array $data,
        array $headers = ['HTTP_ACCEPT' => 'application/json', 'Content-Type' => 'application/json']
    ): Request {
        $uri = new Uri('', '', 80, $path);
        $json = json_encode($data);
        $stream = (new StreamFactory())->createStream($json);
    
        $h = new Headers();
        foreach ($headers as $name => $value) {
            $h->addHeader($name, $value);
        }
    
        return (new SlimRequest($method, $uri, $h, [], [], $stream))
            ->withParsedBody($data);
    }    
    
}
