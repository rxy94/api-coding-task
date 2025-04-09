<?php

declare(strict_types=1);

use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Infrastructure\Persistence\Cache\CachedMySQLCharacterRepository;
use App\Character\Infrastructure\Persistence\Pdo\MySQLCharacterRepository;
use App\Character\Domain\Exception\CharacterNotFoundException;
use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Redis;

class CachedMySQLCharacterRepositoryTest extends TestCase
{
    private Redis $redis;
    private CachedMySQLCharacterRepository $cachedRepository;
    private MySQLCharacterRepository $mysqlRepository;
    private PDO $pdo;
    private array $insertedCharacterIds = [];
    private array $insertedEquipmentIds = [];
    private array $insertedFactionIds = [];

    protected function setUp(): void
    {
        # Inicializamos la conexión con Redis y limpia la caché para asegurar un estado limpio.
        $this->redis = new Redis();
        $this->redis->connect("redis", 6379);
        $this->redis->flushAll();

        $this->pdo = $this->createPdoConnection();
        
        // Crear equipos de prueba
        $this->pdo->exec("INSERT INTO equipments (name, type, made_by) VALUES ('Sword of Testing', 'Weapon', 'Test Blacksmith')");
        $this->insertedEquipmentIds[] = (int)$this->pdo->lastInsertId();

        // Crear facciones de prueba
        $this->pdo->exec("INSERT INTO factions (faction_name, description) VALUES ('Test Faction', 'A test faction for testing')");
        $this->insertedFactionIds[] = (int)$this->pdo->lastInsertId();

        $this->initializeRepository();
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

        $this->redis->flushAll();
    }

    private function initializeRepository(): void
    {
        $this->mysqlRepository = new MySQLCharacterRepository($this->pdo);
        $logger = new NullLogger();

        $this->cachedRepository = new CachedMySQLCharacterRepository(
            $this->mysqlRepository,
            $this->redis,
            $logger
        );
    }

    /**
     * @test
     * @group integration
     * @group character-cache
     * @group character
     */
    public function testGetCharacterById(): void
    {
        # Se guarda un nuevo personaje.
        $character = $this->cachedRepository->save(new Character(
            "John Doe",
            "1990-01-01",
            "Human",
            $this->insertedEquipmentIds[0],
            $this->insertedFactionIds[0]
        ));
        $this->insertedCharacterIds[] = (int)$character->getId();

        # Se realiza la primera recuperación para que se almacene en caché.
        $this->cachedRepository->findById($character->getId());

        # Verificar que la clave existe en Redis.
        $key = $this->getKey($this->cachedRepository, $character);
        $this->assertTrue((bool)$this->redis->exists($key), "La clave '$key' no se encontró en Redis.");

        # Escapamos el patrón para que sea válido para Redis.
        $pattern = str_replace('\\', '\\\\', $key);
        $this->assertCount(1, $this->redis->keys($pattern));

        # Se recupera nuevamente el personaje para comprobar que se obtiene desde la caché.
        $retrievedCharacter = $this->cachedRepository->findById($character->getId());

        # Se asegura que el personaje recuperado sea igual al que se guardó inicialmente.
        $this->assertEquals($character, $retrievedCharacter);
    }

    private function getKey(
        CharacterRepository $repository,
        Character $character
    ): string {
        return get_class($repository) . ":" . $character->getId();
    }

    private function createPdoConnection(): PDO
    {
        return new PDO("mysql:host=db;dbname=test", "root", "root");
    }

    /**
     * @test
     * @group integration
     * @group character-cache
     * @group character
     */
    public function testGetAllCharacters(): void
    {
        // Crea dos personajes para la prueba.
        $character1 = new Character(
            "John Doe",
            "1990-01-01",
            "Human",
            $this->insertedEquipmentIds[0],
            $this->insertedFactionIds[0]
        );
        $character2 = new Character(
            "Jane Doe",
            "1990-01-01",
            "Human",
            $this->insertedEquipmentIds[0],
            $this->insertedFactionIds[0]
        );

        // Guarda los personajes mediante el repositorio caché.
        $savedCharacter1 = $this->cachedRepository->save($character1);
        $savedCharacter2 = $this->cachedRepository->save($character2);
        $this->insertedCharacterIds[] = (int)$savedCharacter1->getId();
        $this->insertedCharacterIds[] = (int)$savedCharacter2->getId();

        // Llama a findAll() para obtener todos los personajes y cachear el conjunto.
        $characters = $this->cachedRepository->findAll();

        // Verifica que se hayan obtenido 2 personajes.
        $this->assertCount(2, $characters);
        $this->assertEquals($savedCharacter1, $characters[0]);
        $this->assertEquals($savedCharacter2, $characters[1]);

        // Verifica que los personajes se almacenan en caché individualmente.
        $key1 = $this->getKey($this->cachedRepository, $savedCharacter1);
        $key2 = $this->getKey($this->cachedRepository, $savedCharacter2);
        $this->assertTrue((bool)$this->redis->exists($key1), "El personaje 1 no se almacenó en caché.");
        $this->assertTrue((bool)$this->redis->exists($key2), "El personaje 2 no se almacenó en caché.");

        // Verifica que la caché del conjunto "all" se ha generado y se utiliza en una segunda llamada.
        $retrievedCharacters = $this->cachedRepository->findAll();
        $this->assertEquals($characters, $retrievedCharacters);
    }

    /**
     * @test
     * @group integration
     * @group character-cache
     * @group character
     */
    public function testDeleteCharacter(): void
    {
        $character = new Character(
            "John Doe",
            "1990-01-01",
            "Human",
            $this->insertedEquipmentIds[0],
            $this->insertedFactionIds[0]
        );

        // Se guarda el personaje.
        $savedCharacter = $this->cachedRepository->save($character);
        $this->insertedCharacterIds[] = (int)$savedCharacter->getId();

        // Se elimina el personaje.
        $result = $this->cachedRepository->delete($savedCharacter);
        $this->assertTrue($result);

        // Se verifica que se haya eliminado de la caché.
        $key = $this->getKey($this->cachedRepository, $savedCharacter);
        $this->assertFalse((bool)$this->redis->exists($key));

        // Se espera que al llamar a findById() se lance la excepción de "CharacterNotFoundException".
        $this->expectException(CharacterNotFoundException::class);

        // Al intentar recuperar el personaje, se lanza la excepción.
        $this->cachedRepository->findById($savedCharacter->getId());
    }
}