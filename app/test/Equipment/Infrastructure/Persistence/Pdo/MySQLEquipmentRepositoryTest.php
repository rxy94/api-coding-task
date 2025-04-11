<?php

namespace App\Test\Equipment\Infrastructure\Persistence\Pdo;

use App\Equipment\Domain\Equipment;
use App\Equipment\Infrastructure\Persistence\Pdo\MySQLEquipmentRepository;
use App\Equipment\Domain\Exception\EquipmentNotFoundException;
use PDO;
use PHPUnit\Framework\TestCase;

class MySQLEquipmentRepositoryTest extends TestCase
{
    private PDO $pdo;
    private MySQLEquipmentRepository $repository;
    private array $insertedEquipmentIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = $this->createPdoConnection();
        $this->repository = new MySQLEquipmentRepository($this->pdo);
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

        parent::tearDown();
    }

    private function createPdoConnection(): PDO
    {
        return new PDO('mysql:host=db;dbname=test', 'root', 'root');
    }

    /**
     * @test
     * @group happy-path
     * @group integration
     * @group equipment
     * @group equipment-repository
     */
    public function givenARepositoryWithOneEquipmentIdWhenReadEquipmentThenReturnTheEquipment()
    {
        // Arrange
        $equipment = new Equipment(
            'Sword of the King',
            'A sword with a hilt of gold and a blade of steel',
            'John Doe',
        );

        // Act
        $savedEquipment = $this->repository->save($equipment);
        $this->insertedEquipmentIds[] = $savedEquipment->getId();
        $foundEquipment = $this->repository->findById($savedEquipment->getId());

        // Assert
        $this->assertEquals('Sword of the King', $foundEquipment->getName());
        $this->assertEquals('A sword with a hilt of gold and a blade of steel', $foundEquipment->getType());    
        $this->assertEquals('John Doe', $foundEquipment->getMadeBy());
    }

    /**
     * @test    
     * @group happy-path
     * @group integration
     * @group equipment
     * @group equipment-repository
     */
    public function givenARepositoryWhenCreateEquipmentThenEquipmentIsSaved()
    {
        // Arrange
        $equipment = new Equipment(
            'Sword of the King',
            'A sword with a hilt of gold and a blade of steel',
            'John Doe',
        );

        // Act
        $savedEquipment = $this->repository->save($equipment);
        $this->insertedEquipmentIds[] = $savedEquipment->getId();

        // Assert
        $this->assertNotNull($savedEquipment->getId());
        $this->assertEquals('Sword of the King', $savedEquipment->getName());
        $this->assertEquals('A sword with a hilt of gold and a blade of steel', $savedEquipment->getType());
        $this->assertEquals('John Doe', $savedEquipment->getMadeBy());
    }

    /**
     * @test    
     * @group happy-path
     * @group integration
     * @group equipment
     * @group equipment-repository
     */
    public function givenARepositoryWithEquipmentWhenUpdateEquipmentThenEquipmentIsUpdated()
    {
        // Arrange
        $equipment = new Equipment(
            'Sword of the King',
            'A sword with a hilt of gold and a blade of steel',
            'John Doe',
        );

        // Act
        $savedEquipment = $this->repository->save($equipment);
        $this->insertedEquipmentIds[] = $savedEquipment->getId();

        // Assert
        $this->assertNotNull($savedEquipment->getId()); 

        $updatedEquipment = new Equipment(
            'Updated Sword',
            'An updated sword',
            'Jane Doe',
            id: $savedEquipment->getId()
        );

        // Act
        $result = $this->repository->save($updatedEquipment);
        $foundEquipment = $this->repository->findById($result->getId());

        // Assert
        $this->assertEquals($savedEquipment->getId(), $foundEquipment->getId());
        $this->assertEquals('Updated Sword', $foundEquipment->getName());
        $this->assertEquals('An updated sword', $foundEquipment->getType());
        $this->assertEquals('Jane Doe', $foundEquipment->getMadeBy());
    }

    /**
     * @test
     * @group happy-path
     * @group integration
     * @group equipment
     * @group equipment-repository
     */
    public function givenARepositoryWithEquipmentWhenDeleteEquipmentThenEquipmentIsDeleted()
    {
        // Arrange
        $equipment = new Equipment(
            'Sword of the King',
            'A sword with a hilt of gold and a blade of steel',
            'John Doe',
        );
        $savedEquipment = $this->repository->save($equipment);
        $this->insertedEquipmentIds[] = $savedEquipment->getId();

        // Act
        $result = $this->repository->delete($savedEquipment);

        // Assert
        $this->assertTrue($result);
        $this->expectException(EquipmentNotFoundException::class);
        $this->repository->findById($savedEquipment->getId());
    }

    /**
     * @test
     * @group unhappy-path
     * @group integration
     * @group equipment
     * @group equipment-repository
     */
    public function givenARepositoryWhenFindByIdWithNonExistentIdThenThrowException()
    {
        // Act & Assert
        $this->expectException(EquipmentNotFoundException::class);
        $this->repository->findById(999);
    }

    /**
     * @test
     * @group happy-path
     * @group integration
     * @group equipment
     * @group equipment-repository
     */
    public function givenARepositoryWithMultipleEquipmentsWhenFindAllThenReturnAllEquipments()
    {
        // Arrange
        $equipment1 = new Equipment(
            'Sword of the King',
            'A sword with a hilt of gold and a blade of steel',
            'John Doe',
        );
        $equipment2 = new Equipment(
            'Shield of the King',
            'A shield with a hilt of gold and a blade of steel',
            'Jane Doe',
        );

        $savedEquipment1 = $this->repository->save($equipment1);
        $savedEquipment2 = $this->repository->save($equipment2);
        $this->insertedEquipmentIds[] = $savedEquipment1->getId();
        $this->insertedEquipmentIds[] = $savedEquipment2->getId();

        // Act
        $equipments = $this->repository->findAll();

        // Assert
        $this->assertCount(2, $equipments);
        $this->assertEquals('Sword of the King', $equipments[0]->getName());
        $this->assertEquals('Shield of the King', $equipments[1]->getName());
    }
}
