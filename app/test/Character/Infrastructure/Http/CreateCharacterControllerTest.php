<?php

namespace App\Test\Character\Infrastructure\Http;

use App\Character\Domain\CharacterRepository;
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
    /**
     * @test
     * @group happy-path
     * @group acceptance
     */
    public function givenARequestToTheControllerWithValidDataWhenCreateCharacterThenReturnTheCharacterAsJson()
    {
        $app = $this->getAppInstance();

        $characterData = [
            'name' => 'John Doe',
            'birth_date' => '1990-01-01',
            'kingdom' => 'Kingdom of Spain',
            'equipment_id' => 1,
            'faction_id' => 1
        ];

        // Crear una solicitud con los datos correctos
        $request = $this->createJsonRequest('POST', '/character', $characterData);
        
        // Asegurarse de que el cuerpo de la solicitud se establece correctamente
        $requestBody = json_encode($characterData);
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
        $this->assertArrayHasKey('character', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('El personaje se ha creado correctamente', $responseData['message']);

        // Verificar que el personaje se creó correctamente en la base de datos
        $repository = $app->getContainer()->get(CharacterRepository::class);
        $createdCharacter = $repository->findById($responseData['character']['id']);

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
     */
    public function givenARequestToTheControllerWithInvalidDataWhenCreateCharacterThenReturnErrorAsJson()
    {
        $app = $this->getAppInstance();

        $invalidCharacterData = [
            'name' => '', // Nombre vacío (inválido)
            'birth_date' => '1990-01-01',
            'kingdom' => 'Kingdom of Spain',
            'equipment_id' => 1,
            'faction_id' => 1
        ];

        $request = $this->createJsonRequest('POST', '/character', $invalidCharacterData);
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
     */
    public function givenARequestToTheControllerWithMissingFieldsWhenCreateCharacterThenReturnErrorAsJson()
    {
        $app = $this->getAppInstance();

        $incompleteCharacterData = [
            'name' => 'John Doe',
            'birth_date' => '1990-01-01',
            // Falta kingdom
            'equipment_id' => 1,
            'faction_id' => 1
        ];

        $request = $this->createJsonRequest('POST', '/character', $incompleteCharacterData);
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
     */
    public function givenARequestToTheControllerWithValidationExceptionWhenCreateCharacterThenReturnErrorAsJson()
    {
        $app = $this->getAppInstance();

        // Datos que pasarán la validación inicial pero fallarán en la validación del dominio
        // Por ejemplo, un nombre demasiado largo que generará una CharacterValidationException
        $invalidCharacterData = [
            'name' => str_repeat('a', 101), // Nombre con más de 100 caracteres
            'birth_date' => '1990-01-01',
            'kingdom' => 'Kingdom of Spain',
            'equipment_id' => 1,
            'faction_id' => 1
        ];

        $request = $this->createJsonRequest('POST', '/character', $invalidCharacterData);
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);
        
        // Depuración
        echo "Status Code: " . $response->getStatusCode() . "\n";
        echo "Response Body: " . $payload . "\n";

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('El nombre no puede exceder los 100 caracteres', $responseData['error']);
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

