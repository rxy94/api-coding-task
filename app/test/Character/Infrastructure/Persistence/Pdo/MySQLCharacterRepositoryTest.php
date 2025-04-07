<?php

namespace App\Test\Character\Infrastructure\Persistence\Pdo;

use App\Character\Domain\Character;
use App\Character\Infrastructure\Persistence\Pdo\MySQLCharacterRepository;
use App\Character\Infrastructure\Persistence\Pdo\Exception\CharacterNotFoundException;
use PDO;
use PHPUnit\Framework\TestCase;

class MySQLCharacterRepositoryTest extends TestCase
{
    private PDO $pdo;
    private MySQLCharacterRepository $repository;
    # Arrays de IDs insertados que nos permiten controlar los IDs insertados y eliminarlos en cada test
    private array $insertedCharacterIds = [];
    private array $insertedEquipmentIds = [];
    private array $insertedFactionIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = $this->createPdoConnection();
        $this->repository = new MySQLCharacterRepository($this->pdo);
        
        // Crear 2 equipos de prueba que serán usados por todos los tests
        $this->pdo->exec("INSERT INTO equipments (id, name, type, made_by) VALUES (2, 'Sword of Testing', 'Weapon', 'Test Blacksmith')");
        $this->insertedEquipmentIds[] = 2;

        $this->pdo->exec("INSERT INTO equipments (id, name, type, made_by) VALUES (3, 'Sword of Testing 2', 'Weapon', 'Test Blacksmith 2')");
        $this->insertedEquipmentIds[] = 3;

        // Crear 2 facciones de prueba que serán usadas por todos los tests
        $this->pdo->exec("INSERT INTO factions (id, faction_name, description) VALUES (2, 'Test Faction', 'A test faction for testing')");
        $this->insertedFactionIds[] = 2;

        $this->pdo->exec("INSERT INTO factions (id, faction_name, description) VALUES (3, 'Test Faction 2', 'A test faction for testing 2')");
        $this->insertedFactionIds[] = 3;
    }

    protected function tearDown(): void
    {
        try {
            // Eliminar solo los registros que hemos insertado en este test
            if (!empty($this->insertedCharacterIds)) {
                $ids = implode(',', $this->insertedCharacterIds);
                $this->pdo->exec("DELETE FROM characters WHERE id IN ($ids)");
            }

            if (!empty($this->insertedEquipmentIds)) {
                $ids = implode(',', $this->insertedEquipmentIds);
                $this->pdo->exec("DELETE FROM equipments WHERE id IN ($ids)");
            }

            if (!empty($this->insertedFactionIds)) {
                $ids = implode(',', $this->insertedFactionIds);
                $this->pdo->exec("DELETE FROM factions WHERE id IN ($ids)");
            }
        } catch (\Exception $e) {
            // Si hay algún error al limpiar, lo registramos pero no lo propagamos
            // para no enmascarar el error original del test
            error_log("Error al limpiar registros en tearDown: " . $e->getMessage());
        } finally {
            // Limpiar los arrays para el siguiente test
            $this->insertedCharacterIds = [];
            $this->insertedEquipmentIds = [];
            $this->insertedFactionIds = [];
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
     * @group character
     * @group repository
     */
    public function givenARepositoryWithOneCharacterIdWhenReadCharacterThenReturnTheCharacter()
    {
        $character = new Character(
            'Test Character',
            '1990-01-01',
            'Kingdom of Spain',
            2,
            2,
        );

        $savedCharacter = $this->repository->save($character);
        $this->insertedCharacterIds[] = $savedCharacter->getId();

        $foundCharacter = $this->repository->findById($savedCharacter->getId());

        $this->assertEquals('Test Character', $foundCharacter->getName());
        $this->assertEquals('1990-01-01', $foundCharacter->getBirthDate());
        $this->assertEquals('Kingdom of Spain', $foundCharacter->getKingdom());
        $this->assertEquals(2, $foundCharacter->getEquipmentId());
        $this->assertEquals(2, $foundCharacter->getFactionId());
    }

    /**
     * @test
     * @group happy-path
     * @group integration
     * @group character
     * @group repository
     */
    public function givenARepositoryWhenCreateCharacterThenCharacterIsSaved()
    {
        // Arrange
        $character = new Character(
            'Jane Doe',
            '1992-02-02',
            'Kingdom of France',
            2,
            2,
        );

        // Act
        $savedCharacter = $this->repository->save($character);
        $this->insertedCharacterIds[] = $savedCharacter->getId();

        // Assert
        $this->assertNotNull($savedCharacter->getId());
        $this->assertEquals('Jane Doe', $savedCharacter->getName());
        $this->assertEquals('1992-02-02', $savedCharacter->getBirthDate());
        $this->assertEquals('Kingdom of France', $savedCharacter->getKingdom());
        $this->assertEquals(2, $savedCharacter->getEquipmentId());
        $this->assertEquals(2, $savedCharacter->getFactionId());
    }

    /**
     * @test
     * @group happy-path
     * @group integration
     * @group character
     * @group repository
     */
    public function givenARepositoryWithCharacterWhenUpdateCharacterThenCharacterIsUpdated()
    {
        // Arrange
        $character = new Character(
            'John Doe',
            '1990-01-01',
            'Kingdom of Spain',
            2,
            2,
        );
        $savedCharacter = $this->repository->save($character);
        $this->insertedCharacterIds[] = $savedCharacter->getId();
        
        $updatedCharacter = new Character(
            'John Updated',
            '1995-05-05',
            'Kingdom of Portugal',
            2,
            2,
            $savedCharacter->getId()
        );

        // Act
        $result = $this->repository->save($updatedCharacter);
        $foundCharacter = $this->repository->findById($result->getId());

        // Assert
        $this->assertEquals($savedCharacter->getId(), $foundCharacter->getId());
        $this->assertEquals('John Updated', $foundCharacter->getName());
        $this->assertEquals('1995-05-05', $foundCharacter->getBirthDate());
        $this->assertEquals('Kingdom of Portugal', $foundCharacter->getKingdom());
        $this->assertEquals(2, $foundCharacter->getEquipmentId());
        $this->assertEquals(2, $foundCharacter->getFactionId());
    }

    /**
     * @test
     * @group happy-path
     * @group integration
     * @group character
     * @group repository
     */
    public function givenARepositoryWithCharacterWhenDeleteCharacterThenCharacterIsDeleted()
    {
        // Arrange
        $character = new Character(
            'John Doe',
            '1990-01-01',
            'Kingdom of Spain',
            2,
            2,
        );
        $savedCharacter = $this->repository->save($character);

        // Act
        $result = $this->repository->delete($savedCharacter);

        // Assert
        $this->assertTrue($result);
        $this->expectException(CharacterNotFoundException::class);
        $this->repository->findById($savedCharacter->getId());
    }

    /**
     * @test
     * @group unhappy-path
     * @group integration
     * @group character
     * @group repository
     */
    public function givenARepositoryWhenFindByIdWithNonExistentIdThenThrowException()
    {
        // Act & Assert
        $this->expectException(CharacterNotFoundException::class);
        $this->repository->findById(999);
    }

    /**
     * @test
     * @group happy-path
     * @group integration
     * @group character
     * @group repository
     */
    public function givenARepositoryWithMultipleCharactersWhenFindAllThenReturnAllCharacters()
    {
        // Arrange
        $character1 = new Character(
            'John Doe',
            '1990-01-01',
            'Kingdom of Spain',
            2,
            2,
        );
        $character2 = new Character(
            'Jane Doe',
            '1992-02-02',
            'Kingdom of France',
            2,
            2,
        );
        
        $savedCharacter1 = $this->repository->save($character1);
        $savedCharacter2 = $this->repository->save($character2);
        $this->insertedCharacterIds[] = $savedCharacter1->getId();
        $this->insertedCharacterIds[] = $savedCharacter2->getId();

        // Act
        $characters = $this->repository->findAll();

        // Assert
        $this->assertCount(2, $characters);
        $this->assertEquals('John Doe', $characters[0]->getName());
        $this->assertEquals('Jane Doe', $characters[1]->getName());
    }
    
}
