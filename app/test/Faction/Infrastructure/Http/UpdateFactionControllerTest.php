<?php

namespace App\Test\Faction\Infrastructure\Http;

use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionRepository;
use App\Faction\Infrastructure\Persistence\Pdo\Exception\FactionNotFoundException;
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

class UpdateFactionControllerTest extends TestCase
{
    private App $app;
    private FactionRepository $repository;
    private array $insertedFactionIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = $this->getAppInstance();
        $this->repository = $this->app->getContainer()->get(FactionRepository::class);
    }

    protected function tearDown(): void
    {
        try {
            if (!empty($this->insertedFactionIds)) {
                $pdo = $this->app->getContainer()->get(\PDO::class);
                $ids = implode(',', $this->insertedFactionIds);
                $pdo->exec("DELETE FROM factions WHERE id IN ($ids)");
            }
        } catch (\Exception $e) {
            error_log("Error al limpiar registros en tearDown: " . $e->getMessage());
        } finally {
            $this->insertedFactionIds = [];
        }

        parent::tearDown();
    }
    
    /**
     * @test
     * @group happy-path
     * @group acceptance
     * @group faction
     * @group controller
     */ 
    public function givenARequestToTheControllerWithValidDataWhenUpdateFactionThenReturnTheFactionAsJson()
    {
        // Crear una facción para actualizarla
        $originalFaction = new Faction(
            'Kingdom of Spain',
            'A powerful kingdom in the south of Europe'
        );

        $savedFaction = $this->repository->save($originalFaction);
        $this->insertedFactionIds[] = $savedFaction->getId();
        $factionId = $savedFaction->getId();
        
        // Datos para actualizar la facción
        $updateData = [
            'faction_name' => 'Kingdom of Portugal',
            'description' => 'A powerful kingdom in the north of Europe'
        ];

        // Crear una solicitud con los datos correctos
        $request = $this->createJsonRequest('PUT', '/factions/' . $factionId, $updateData);
        
        // Asegurarse de que el cuerpo de la solicitud se establece correctamente
        $requestBody = json_encode($updateData);
        $stream = (new StreamFactory())->createStream($requestBody);
        $request = $request->withBody($stream);

        // Procesar la solicitud
        $response = $this->app->handle($request);

        // Obtener y decodificar la respuesta
        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('faction', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('La facción se ha actualizado correctamente', $responseData['message']);

        // Verificar que la facción se actualizó correctamente en la base de datos
        $updatedFaction = $this->repository->findById($factionId);

        $this->assertEquals($updateData['faction_name'], $updatedFaction->getName());
        $this->assertEquals($updateData['description'], $updatedFaction->getDescription());
    }

    private function getAppInstance(): App
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../../../');
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

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group faction
     * @group controller
     */
    public function givenARequestToTheControllerWithInvalidDataWhenUpdateFactionThenReturnValidationError()
    {
        // Crear una facción para actualizarla
        $originalFaction = new Faction(
            'Kingdom of Spain',
            'A powerful kingdom in the south of Europe'
        );

        $savedFaction = $this->repository->save($originalFaction);
        $this->insertedFactionIds[] = $savedFaction->getId();
        $factionId = $savedFaction->getId();
        
        // Datos inválidos para actualizar la facción
        $updateData = [
            'faction_name' => '', // Nombre vacío
            'description' => 'A powerful kingdom in the north of Europe'
        ];
        
        // Crear una solicitud con los datos inválidos
        $request = $this->createJsonRequest('PUT', '/factions/' . $factionId, $updateData);
        $requestBody = json_encode($updateData);
        $stream = (new StreamFactory())->createStream($requestBody);
        $request = $request->withBody($stream);
        
        // Procesar la solicitud
        $response = $this->app->handle($request);

        // Verificar la respuesta
        $this->assertEquals(400, $response->getStatusCode());
        $responseData = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('error', $responseData);  
    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group faction
     * @group controller
     */
    public function givenARequestToTheControllerWithNonExistentFactionWhenUpdateFactionThenReturnNotFoundError()
    {
        // Datos para actualizar la facción
        $updateData = [
            'faction_name' => 'Kingdom of Portugal',
            'description' => 'A powerful kingdom in the north of Europe'
        ];

        // Crear una solicitud con un ID que no existe
        $request = $this->createJsonRequest('PUT', '/factions/999', $updateData);
        $requestBody = json_encode($updateData);
        $stream = (new StreamFactory())->createStream($requestBody);
        $request = $request->withBody($stream);

        // Procesar la solicitud
        $response = $this->app->handle($request);

        // Verificar la respuesta
        $this->assertEquals(404, $response->getStatusCode());
        $responseData = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('error', $responseData);
    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group faction
     * @group controller
     */
    public function givenARequestToTheControllerWithMissingDataWhenUpdateFactionThenReturnValidationError()
    {
        // Crear una facción para actualizarla
        $originalFaction = new Faction(
            'Kingdom of Spain',
            'A powerful kingdom in the south of Europe'
        );

        $savedFaction = $this->repository->save($originalFaction);
        $this->insertedFactionIds[] = $savedFaction->getId();
        $factionId = $savedFaction->getId();

        // Datos incompletos para actualizar la facción
        $updateData = [
            'faction_name' => 'Kingdom of Portugal',
            // Falta description
        ];

        // Crear una solicitud con datos faltantes
        $request = $this->createJsonRequest('PUT', '/factions/' . $factionId, $updateData);
        $requestBody = json_encode($updateData);
        $stream = (new StreamFactory())->createStream($requestBody);
        $request = $request->withBody($stream);

        // Procesar la solicitud
        $response = $this->app->handle($request);

        // Verificar la respuesta
        $this->assertEquals(400, $response->getStatusCode());
        $responseData = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('error', $responseData);
    }   

}