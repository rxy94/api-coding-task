<?php

namespace App\Test\Faction\Infrastructure\Http;

use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionRepository;
use App\Faction\Domain\FactionToArrayTransformer;

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

class ReadFactionControllerTest extends TestCase
{
    /**
     * @test
     * @group happy-path
     * @group integration
     * @group faction
     * @group controller
     * @group ruyi
     */
    public function testGivenARequestToTheControllerWithOneFactionIdWhenReadFactionThenReturnTheFactionAsJson()
    {
        $app = $this->getAppInstance();

        $repository = $app->getContainer()->get(FactionRepository::class);

        $expectedFaction = new Faction(
            'Kingdom of Spain',
            'A powerful kingdom in the south of Europe'
        );
        
        $savedFaction = $repository->save($expectedFaction);

        $request = $this->createRequest('GET', '/factions/' . $savedFaction->getId());
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $serializedPayload = json_encode([
            'faction' => FactionToArrayTransformer::transform($savedFaction),
            'message' => 'Facción encontrada correctamente'
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