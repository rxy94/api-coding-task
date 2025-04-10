<?php

namespace App\Equipment\Infrastructure\Persistence\Cache;

use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Domain\Exception\EquipmentNotFoundException;
use App\Equipment\Infrastructure\Persistence\Pdo\MySQLEquipmentRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use PDO;
use Redis;

class CachedMySQLEquipmentRepositoryTest extends TestCase
{
    private Redis $redis;
    private CachedMySQLEquipmentRepository $cachedRepository;
    private MySQLEquipmentRepository $mysqlRepository;
    private PDO $pdo;
    private array $insertedEquipmentIds = [];

    protected function setUp(): void
    {
        $this->redis = new Redis();
        $this->redis->connect("redis", 6379);
        $this->redis->flushAll();

        $this->pdo = $this->createPdoConnection();
        $this->initializeRepository();
    }
    
    protected function tearDown(): void
    {
        try {
            if (!empty($this->insertedEquipmentIds)) {
                $ids = implode(',', $this->insertedEquipmentIds);
                $this->pdo->exec("DELETE FROM equipments WHERE id IN ($ids)");
            }
        } catch (\Exception $e) {
            error_log("Error al limpiar registros en tearDown: " . $e->getMessage());
        } finally {
            $this->insertedEquipmentIds = [];
        }

        $this->redis->flushAll();
    }

    private function initializeRepository(): void
    {
        $this->mysqlRepository = new MySQLEquipmentRepository($this->pdo);
        $logger = new NullLogger();

        $this->cachedRepository = new CachedMySQLEquipmentRepository(
            $this->mysqlRepository, 
            $this->redis, $logger
        );
    }

    private function createPdoConnection(): PDO
    {
        return new PDO('mysql:host=db;dbname=test', 'root', 'root');
    }

    private function getKey(
        EquipmentRepository $repository, 
        Equipment $equipment
    ): string {
        return get_class($repository) . ":" . $equipment->getId();
    }
    
    /**
     * @test
     * @group integration
     * @group equipment-cache
     * @group equipment
     */
    public function testGetEquipmentById(): void
    {
        $equipment = $this->cachedRepository->save(new Equipment(
            "Sword of the King",
            "A sword with a hilt of gold and a blade of steel",
            "John Doe"
        ));
        $this->insertedEquipmentIds[] = (int)$equipment->getId();

        $this->cachedRepository->findById($equipment->getId());

        $key = $this->getKey($this->cachedRepository, $equipment);
        $this->assertTrue((bool)$this->redis->exists($key), "La clave '$key' no se encontró en Redis.");

        $pattern = str_replace('\\', '\\\\', $key);
        $this->assertCount(1, $this->redis->keys($pattern));

        $retrievedEquipment = $this->cachedRepository->findById($equipment->getId());

        $this->assertEquals($equipment, $retrievedEquipment);
    }
    
    /**
     * @test
     * @group integration
     * @group equipment-cache
     * @group equipment
     */
    public function testGetAllEquipments(): void
    {
        $equipment1 = new Equipment(
            "Sword of the King",
            "A sword with a hilt of gold and a blade of steel",
            "John Doe"
        );
        $equipment2 = new Equipment(
            "Shield of the King",
            "A shield with a hilt of gold and a shield of steel",
            "John Doe"
        );

        $savedEquipment1 = $this->cachedRepository->save($equipment1);
        $savedEquipment2 = $this->cachedRepository->save($equipment2);
        $this->insertedEquipmentIds[] = (int)$savedEquipment1->getId();
        $this->insertedEquipmentIds[] = (int)$savedEquipment2->getId();

        $equipments = $this->cachedRepository->findAll();

        $this->assertCount(2, $equipments);
        $this->assertEquals($savedEquipment1, $equipments[0]);
        $this->assertEquals($savedEquipment2, $equipments[1]);

        $key1 = $this->getKey($this->cachedRepository, $savedEquipment1);
        $key2 = $this->getKey($this->cachedRepository, $savedEquipment2);
        $this->assertTrue((bool)$this->redis->exists($key1), "El equipamiento 1 no se almacenó en caché.");
        $this->assertTrue((bool)$this->redis->exists($key2), "El equipamiento 2 no se almacenó en caché.");

        $retrievedEquipments = $this->cachedRepository->findAll();  
        $this->assertEquals($equipments, $retrievedEquipments);
    }
    
    /**
     * @test
     * @group integration
     * @group equipment-cache
     * @group equipment
     */
    public function testDeleteEquipment(): void
    {
        $equipment = new Equipment(
            "Sword of the King",
            "A sword with a hilt of gold and a blade of steel",
            "John Doe"
        );

        $savedEquipment = $this->cachedRepository->save($equipment);
        $this->insertedEquipmentIds[] = (int)$savedEquipment->getId();

        $result = $this->cachedRepository->delete($savedEquipment);
        $this->assertTrue($result);

        $key = $this->getKey($this->cachedRepository, $savedEquipment);
        $this->assertFalse((bool)$this->redis->exists($key));

        $this->expectException(EquipmentNotFoundException::class);
        $this->cachedRepository->findById($savedEquipment->getId());
    }
    
}