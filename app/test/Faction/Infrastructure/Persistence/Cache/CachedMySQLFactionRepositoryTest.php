<?php

declare(strict_types=1);

use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionRepository;
use App\Faction\Infrastructure\Persistence\Cache\CachedMySQLFactionRepository;
use App\Faction\Infrastructure\Persistence\Pdo\MySQLFactionRepository;
use App\Faction\Domain\Exception\FactionNotFoundException;
use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Redis;

class CachedMySQLFactionRepositoryTest extends TestCase
{
    private Redis $redis;
    private CachedMySQLFactionRepository $cachedRepository;
    private MySQLFactionRepository $mysqlRepository;
    private PDO $pdo;
    private array $insertedFactionIds = [];

    protected function setUp(): void
    {
        # Inicializamos la conexión con Redis y limpia la caché para asegurar un estado limpio.
        $this->redis = new Redis();
        $this->redis->connect("redis", 6379);
        $this->redis->flushAll();

        $this->pdo = $this->createPdoConnection();
        $this->initializeRepository();
    }

    protected function tearDown(): void
    {
        try {
            if (!empty($this->insertedFactionIds)) {
                $ids = implode(',', $this->insertedFactionIds);
                $this->pdo->exec("DELETE FROM factions WHERE id IN ($ids)");
            }
        } catch (\Exception $e) {
            error_log("Error al limpiar registros en tearDown: " . $e->getMessage());
        } finally {
            $this->insertedFactionIds = [];
        }

        $this->redis->flushAll();
    }

    private function initializeRepository(): void
    {
        $this->mysqlRepository = new MySQLFactionRepository($this->pdo);
        $logger = new NullLogger();

        $this->cachedRepository = new CachedMySQLFactionRepository(
            $this->mysqlRepository,
            $this->redis,
            $logger
        );
    }

    /**
     * @test
     * @group integration
     * @group faction-cache
     * @group faction
     */
    public function testGetFactionById(): void
    {
        # Se guarda una nueva facción.
        $faction = $this->cachedRepository->save(new Faction(
            "Kingdom of Spain",
            "A powerful kingdom in the south of Europe"
        ));
        $this->insertedFactionIds[] = (int)$faction->getId();

        # Se realiza la primera recuperación para que se almacene en caché.
        $this->cachedRepository->findById($faction->getId());

        # Verificar que la clave existe en Redis.
        $key = $this->getKey($this->cachedRepository, $faction);
        $this->assertTrue((bool)$this->redis->exists($key), "La clave '$key' no se encontró en Redis.");

        # Escapamos el patrón para que sea válido para Redis.
        $pattern = str_replace('\\', '\\\\', $key);
        $this->assertCount(1, $this->redis->keys($pattern));

        # Se recupera nuevamente la facción para comprobar que se obtiene desde la caché.
        $retrievedFaction = $this->cachedRepository->findById($faction->getId());

        # Se asegura que la facción recuperada sea igual a la que se guardó inicialmente.
        $this->assertEquals($faction, $retrievedFaction);
    }

    private function getKey(
        FactionRepository $repository,
        Faction $faction
    ): string {
        return get_class($repository) . ":" . $faction->getId();
    }

    private function createPdoConnection(): PDO
    {
        return new PDO("mysql:host=db;dbname=test", "root", "root");
    }

    /**
     * @test
     * @group integration
     * @group faction-cache
     * @group faction
     */
    public function testGetAllFactions(): void
    {
        // Crea dos facciones para la prueba.
        $faction1 = new Faction(
            "Kingdom of Spain",
            "A powerful kingdom in the south of Europe"
        );
        $faction2 = new Faction(
            "Kingdom of France",
            "A powerful kingdom in the west of Europe"
        );

        // Guarda las facciones mediante el repositorio caché.
        $savedFaction1 = $this->cachedRepository->save($faction1);
        $savedFaction2 = $this->cachedRepository->save($faction2);
        $this->insertedFactionIds[] = (int)$savedFaction1->getId();
        $this->insertedFactionIds[] = (int)$savedFaction2->getId();

        // Llama a findAll() para obtener todas las facciones y cachear el conjunto.
        $factions = $this->cachedRepository->findAll();

        // Verifica que se hayan obtenido 2 facciones.
        $this->assertCount(2, $factions);
        $this->assertEquals($savedFaction1, $factions[0]);
        $this->assertEquals($savedFaction2, $factions[1]);

        // Verifica que las facciones se almacenan en caché individualmente.
        $key1 = $this->getKey($this->cachedRepository, $savedFaction1);
        $key2 = $this->getKey($this->cachedRepository, $savedFaction2);
        $this->assertTrue((bool)$this->redis->exists($key1), "La facción 1 no se almacenó en caché.");
        $this->assertTrue((bool)$this->redis->exists($key2), "La facción 2 no se almacenó en caché.");

        // Verifica que la caché del conjunto "all" se ha generado y se utiliza en una segunda llamada.
        $retrievedFactions = $this->cachedRepository->findAll();
        $this->assertEquals($factions, $retrievedFactions);
    }

    /**
     * @test
     * @group integration
     * @group faction-cache
     * @group faction
     */
    public function testDeleteFaction(): void
    {
        $faction = new Faction(
            "Kingdom of Spain",
            "A powerful kingdom in the south of Europe"
        );

        // Se guarda la facción.
        $savedFaction = $this->cachedRepository->save($faction);
        $this->insertedFactionIds[] = (int)$savedFaction->getId();

        // Se elimina la facción.
        $result = $this->cachedRepository->delete($savedFaction);
        $this->assertTrue($result);

        // Se verifica que se haya eliminado de la caché.
        $key = $this->getKey($this->cachedRepository, $savedFaction);
        $this->assertFalse((bool)$this->redis->exists($key));

        // Se espera que al llamar a findById() se lance la excepción de "FactionNotFoundException".
        $this->expectException(FactionNotFoundException::class);

        // Al intentar recuperar la facción, se lanza la excepción.
        $this->cachedRepository->findById($savedFaction->getId());
    }
}
