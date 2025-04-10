<?php

namespace App\Test\Character\Infrastructure\Http;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Infrastructure\Persistence\Pdo\Exception\CharacterNotFoundException;
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
     * @group update-character
     */
    public function givenARequestToTheControllerWithValidDataWhenUpdateCharacterThenReturnTheUpdatedCharacterAsJson()
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

        // Datos para actualizar
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
        $this->assertEquals('El personaje se ha actualizado correctamente', $responseData['message']);

        // Verificar que el personaje se actualizó correctamente en la base de datos
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
        $this->assertEquals('El nombre es requerido', $responseData['error']);
    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group character
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
