<?php

namespace App\Test\Faction\Infrastructure\Http;

use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionRepository;
use App\Faction\Domain\FactionToArrayTransformer;
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

class DeleteFactionControllerTest extends TestCase
{
    /**
     * @test
     * @group happy-path
     * @group acceptance
     * @group delete-faction
     */
    public function givenARequestToTheControllerWithValidIdWhenDeleteFactionThenReturnSuccessMessage()
    {
        $app = $this->getAppInstance();
        $repository = $app->getContainer()->get(FactionRepository::class);

        $faction = new Faction(
            'Kingdom of Spain',
            'A powerful kingdom in the south of Europe'
        );
        
        $savedFaction = $repository->save($faction);
        $factionId = $savedFaction->getId();

        $request = $this->createRequest('DELETE', '/factions/' . $factionId);
        $response = $app->handle($request);
        
        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Facción eliminada correctamente', $responseData['message']);

        $this->expectException(FactionNotFoundException::class);
        $repository->findById($factionId);
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

    /**
     * @test
     * @group unhappy-path
     * @group acceptance
     * @group delete-faction
     */
    public function givenARequestToTheControllerWithNonExistentIdWhenDeleteFactionThenReturnErrorAsJson()
    {
        $app = $this->getAppInstance();

        $nonExistentId = 999;

        $request = $this->createRequest('DELETE', '/factions/' . $nonExistentId);
        $response = $app->handle($request);

        $payload = (string) $response->getBody();
        $responseData = json_decode($payload, true);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('Error al eliminar la facción', $responseData['error']);

    }
}
