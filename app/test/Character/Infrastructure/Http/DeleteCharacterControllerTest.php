<?php

namespace App\Test\Character\Infrastructure\Http;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
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

class DeleteCharacterControllerTest extends TestCase
{
    /**
     * @test
     * @group happy-path
     * @group acceptance
     * @group delete-character
     */
    public function givenARequestToTheControllerWithValidIdWhenDeleteCharacterThenReturnSuccessMessage()
    {
        $app = $this->getAppInstance();
        $repository = $app->getContainer()->get(CharacterRepository::class);

        // Crear un personaje para eliminarlo
        $character = new Character(
            'John Doe',
            '1990-01-01',
            'Kingdom of Spain',
            1,
            1
        );
        $savedCharacter = $repository->save($character);
        $characterId = $savedCharacter->getId();

        // Crear una solicitud para eliminar el personaje
        $request = $this->createRequest('DELETE', '/characters/' . $characterId);
        
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
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Personaje eliminado correctamente', $responseData['message']);

        // Verificar que el personaje se eliminó correctamente de la base de datos
        $this->expectException(CharacterNotFoundException::class);
        $repository->findById($characterId);
    }

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group delete-character
     */
    public function givenARequestToTheControllerWithNonExistentIdWhenDeleteCharacterThenReturnErrorAsJson()
    {
        $app = $this->getAppInstance();
        
        // ID de un personaje que no existe
        $nonExistentId = 999;
        
        // Crear una solicitud para eliminar el personaje
        $request = $this->createRequest('DELETE', '/characters/' . $nonExistentId);
        
        // Procesar la solicitud
        $response = $app->handle($request);

        // Obtener y decodificar la respuesta
        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);
        
        // Depuración
        echo "Status Code: " . $response->getStatusCode() . "\n";
        echo "Response Body: " . $payload . "\n";
        
        // Verificar la respuesta
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Error al eliminar el personaje', $responseData['error']);
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
