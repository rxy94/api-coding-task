<?php

namespace App\Test\Character\Infrastructure\Http;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Domain\Exception\CharacterValidationException;
use App\Character\Infrastructure\Persistence\Pdo\Exception\CharacterNotFoundException;
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

class UpdateCharacterControllerTest extends TestCase
{
    /**
     * @test
     * @group happy-path
     * @group acceptance
     * @group update-character
     */
    public function givenARequestToTheControllerWithValidDataWhenUpdateCharacterThenReturnTheCharacterAsJson()
    {
        $app = $this->getAppInstance();
        $repository = $app->getContainer()->get(CharacterRepository::class);

        // Crear un personaje para actualizarlo
        $originalCharacter = new Character(
            'John Doe',
            '1990-01-01',
            'Kingdom of Spain',
            1,
            1
        );
        $savedCharacter = $repository->save($originalCharacter);
        $characterId = $savedCharacter->getId();

        // Datos para actualizar el personaje
        $updateData = [
            'name' => 'Jane Doe',
            'birth_date' => '1992-05-15',
            'kingdom' => 'Kingdom of France',
            'equipment_id' => 1,
            'faction_id' => 1
        ];

        // Crear una solicitud con los datos correctos
        $request = $this->createJsonRequest('PUT', '/character/' . $characterId, $updateData);
        
        // Asegurarse de que el cuerpo de la solicitud se establece correctamente
        $requestBody = json_encode($updateData);
        $stream = (new StreamFactory())->createStream($requestBody);
        $request = $request->withBody($stream);
        
        // Procesar la solicitud
        $response = $app->handle($request);

        // Obtener y decodificar la respuesta
        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);
        
        // Depuración
        echo "Status Code: " . $response->getStatusCode() . "\n";
        echo "Response Body: " . $payload . "\n";
        
        // Verificar la respuesta
        $this->assertEquals(200, $response->getStatusCode());

        // Verificar que el personaje se actualizó correctamente en la base de datos
        $updatedCharacter = $repository->findById($characterId);

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
     */
    public function givenARequestToTheControllerWithInvalidDataWhenUpdateCharacterThenReturnErrorAsJson()
    {
        $app = $this->getAppInstance();
        $repository = $app->getContainer()->get(CharacterRepository::class);

        // Crear un personaje para actualizarlo
        $originalCharacter = new Character(
            'John Doe',
            '1990-01-01',
            'Kingdom of Spain',
            1,
            1
        );
        $savedCharacter = $repository->save($originalCharacter);
        $characterId = $savedCharacter->getId();

        // Datos inválidos para actualizar el personaje
        $invalidUpdateData = [
            'name' => '', // Nombre vacío (inválido)
            'birth_date' => '1992-05-15',
            'kingdom' => 'Kingdom of France',
            'equipment_id' => 1,
            'faction_id' => 1
        ];

        $request = $this->createJsonRequest('PUT', '/character/' . $characterId, $invalidUpdateData);
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals(CharacterValidationException::withNameRequired()->getMessage(), $responseData['error']);
    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group update-character
     */
    public function givenARequestToTheControllerWithMissingFieldsWhenUpdateCharacterThenReturnErrorAsJson()
    {
        $app = $this->getAppInstance();
        $repository = $app->getContainer()->get(CharacterRepository::class);

        // Crear un personaje para actualizarlo
        $originalCharacter = new Character(
            'John Doe',
            '1990-01-01',
            'Kingdom of Spain',
            1,
            1
        );
        $savedCharacter = $repository->save($originalCharacter);
        $characterId = $savedCharacter->getId();

        // Datos incompletos para actualizar el personaje
        $incompleteUpdateData = [
            'name' => 'Jane Doe',
            'birth_date' => '1992-05-15',
            // Falta kingdom
            'equipment_id' => 1,
            'faction_id' => 1
        ];

        $request = $this->createJsonRequest('PUT', '/character/' . $characterId, $incompleteUpdateData);
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(400, $response->getStatusCode());

    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group update-character
     */
    public function givenARequestToTheControllerWithNonExistentIdWhenUpdateCharacterThenReturnErrorAsJson()
    {
        $app = $this->getAppInstance();
        
        // ID de un personaje que no existe
        $nonExistentId = 999;
        
        // Datos para actualizar el personaje
        $updateData = [
            'name' => 'Jane Doe',
            'birth_date' => '1992-05-15',
            'kingdom' => 'Kingdom of France',
            'equipment_id' => 1,
            'faction_id' => 1
        ];

        $request = $this->createJsonRequest('PUT', '/character/' . $nonExistentId, $updateData);
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals(CharacterNotFoundException::build()->getMessage(), $responseData['error']);
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
}
