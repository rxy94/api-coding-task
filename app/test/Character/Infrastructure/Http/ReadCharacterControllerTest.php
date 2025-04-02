<?php

namespace App\Test\Character\Infrastructure\Http;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Domain\CharacterToArrayTransformer;

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
    /**
     * @test
     * @group happy-path
     * @group acceptance
     */
    public function givenARequestToTheControllerWithOneCharacterIdWhenReadCharacterThenReturnTheCharacterAsJson()
    {
        $app = $this->getAppInstance();

        $repository = $app->getContainer()->get(CharacterRepository::class);

        $expectedCharacter = new Character(
            'John Doe',
            '1990-01-01',
            'Kingdom of Spain',
            1,
            1
        );

        $savedCharacter = $repository->save($expectedCharacter);

        $request = $this->createRequest('GET', '/character/' . $savedCharacter->getId());
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $serializedPayload = json_encode([
            'character' => CharacterToArrayTransformer::transform($savedCharacter),
            'message' => 'Personaje obtenido correctamente'
        ]);

        $this->assertEquals($serializedPayload, $payload);

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
