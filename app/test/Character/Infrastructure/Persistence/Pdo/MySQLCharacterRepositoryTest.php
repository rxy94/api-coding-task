<?php

namespace App\Test\Character\Infrastructure\Persistence\Pdo;

use App\Character\Domain\Character;
use App\Character\Domain\Exception\CharacterNotFoundException;
use App\Character\Infrastructure\Persistence\Pdo\MySQLCharacterRepository;
use App\Shared\Infrastructure\Persistence\Pdo\Exception\RowDeletionFailedException;
use App\Shared\Infrastructure\Persistence\Pdo\Exception\RowInsertionFailedException;
use App\Shared\Infrastructure\Persistence\Pdo\Exception\RowUpdateFailedException;
use PDO;
use PHPUnit\Framework\TestCase;

class MySQLCharacterRepositoryTest extends TestCase
{
    private PDO $pdo;
    private MySQLCharacterRepository $repository;
    private array $insertedCharacterIds = [];
    private array $insertedEquipmentIds = [];
    private array $insertedFactionIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = $this->createPdoConnection();
        $this->repository = new MySQLCharacterRepository($this->pdo);
        
        // Crear equipos de prueba
        $this->pdo->exec("INSERT INTO equipments (name, type, made_by) VALUES ('Sword of Testing', 'Weapon', 'Test Blacksmith')");
        $this->insertedEquipmentIds[] = $this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO equipments (name, type, made_by) VALUES ('Sword of Testing 2', 'Weapon', 'Test Blacksmith 2')");
        $this->insertedEquipmentIds[] = $this->pdo->lastInsertId();

        // Crear facciones de prueba
        $this->pdo->exec("INSERT INTO factions (faction_name, description) VALUES ('Test Faction', 'A test faction for testing')");
        $this->insertedFactionIds[] = $this->pdo->lastInsertId();

        $this->pdo->exec("INSERT INTO factions (faction_name, description) VALUES ('Test Faction 2', 'A test faction for testing 2')");
        $this->insertedFactionIds[] = $this->pdo->lastInsertId();
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
     * @group character-repository
     */
    public function givenARepositoryWithOneCharacterIdWhenReadCharacterThenReturnTheCharacter()
    {
        $character = new Character(
            'Test Character',
            '1990-01-01',
            'Kingdom of Spain',
            $this->insertedEquipmentIds[0],
            $this->insertedFactionIds[0],
        );

        $savedCharacter = $this->repository->save($character);
        $this->insertedCharacterIds[] = $savedCharacter->getId();

        $foundCharacter = $this->repository->findById($savedCharacter->getId());

        $this->assertEquals('Test Character', $foundCharacter->getName());
        $this->assertEquals('1990-01-01', $foundCharacter->getBirthDate());
        $this->assertEquals('Kingdom of Spain', $foundCharacter->getKingdom());
        $this->assertEquals($this->insertedEquipmentIds[0], $foundCharacter->getEquipmentId());
        $this->assertEquals($this->insertedFactionIds[0], $foundCharacter->getFactionId());
    }

    /**
     * @test
     * @group happy-path
     * @group integration
     * @group character
     * @group character-repository
     */
    public function givenARepositoryWhenCreateCharacterThenCharacterIsSaved()
    {
        // Arrange
        $character = new Character(
            'Jane Doe',
            '1992-02-02',
            'Kingdom of France',
            $this->insertedEquipmentIds[0],
            $this->insertedFactionIds[0],
        );

        // Act
        $savedCharacter = $this->repository->save($character);
        $this->insertedCharacterIds[] = $savedCharacter->getId();

        // Assert
        $this->assertNotNull($savedCharacter->getId());
        $this->assertEquals('Jane Doe', $savedCharacter->getName());
        $this->assertEquals('1992-02-02', $savedCharacter->getBirthDate());
        $this->assertEquals('Kingdom of France', $savedCharacter->getKingdom());
        $this->assertEquals($this->insertedEquipmentIds[0], $savedCharacter->getEquipmentId());
        $this->assertEquals($this->insertedFactionIds[0], $savedCharacter->getFactionId());
    }

    /**
     * @test
     * @group happy-path
     * @group integration
     * @group character
     * @group character-repository
     */
    public function givenARepositoryWithCharacterWhenUpdateCharacterThenCharacterIsUpdated()
    {
        // Arrange
        $character = new Character(
            'John Doe',
            '1990-01-01',
            'Kingdom of Spain',
            $this->insertedEquipmentIds[0],
            $this->insertedFactionIds[0],
        );
        $savedCharacter = $this->repository->save($character);
        $this->insertedCharacterIds[] = $savedCharacter->getId();
        
        $updatedCharacter = new Character(
            'John Updated',
            '1995-05-05',
            'Kingdom of Portugal',
            $this->insertedEquipmentIds[1],
            $this->insertedFactionIds[1],
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
        $this->assertEquals($this->insertedEquipmentIds[1], $foundCharacter->getEquipmentId());
        $this->assertEquals($this->insertedFactionIds[1], $foundCharacter->getFactionId());
    }

    /**
     * @test
     * @group happy-path
     * @group integration
     * @group character
     * @group character-repository
     */
    public function givenARepositoryWithCharacterWhenDeleteCharacterThenCharacterIsDeleted()
    {
        // Arrange
        $character = new Character(
            'John Doe',
            '1990-01-01',
            'Kingdom of Spain',
            $this->insertedEquipmentIds[0],
            $this->insertedFactionIds[0],
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
     * @group happy-path
     * @group integration
     * @group character
     * @group character-repository
     */
    public function givenARepositoryWithMultipleCharactersWhenFindAllThenReturnAllCharacters()
    {
        // Arrange
        $character1 = new Character(
            'John Doe',
            '1990-01-01',
            'Kingdom of Spain',
            $this->insertedEquipmentIds[0],
            $this->insertedFactionIds[0],
        );
        $character2 = new Character(
            'Jane Doe',
            '1992-02-02',
            'Kingdom of France',
            $this->insertedEquipmentIds[1],
            $this->insertedFactionIds[1],
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

    /**
     * @test
     * @group unhappy-path
     * @group integration
     * @group character
     * @group character-repository
     */
    public function givenARepositoryWhenFindByIdWithNonExistentIdThenThrowException()
    {
        // Act & Assert
        $this->expectException(CharacterNotFoundException::class);
        $this->repository->findById(999);
    }

    /**
     * @test
     * @group unhappy-path
     * @group integration
     * @group character
     * @group character-repository
     */
    public function testRowDeletionFailsUsingExistingPdoConnection(): void
    {
        $character = new Character(
            'Test Character',
            '2000-01-01',
            'Test Kingdom',
            1,
            1,
            999
        );

        $this->pdo->exec("RENAME TABLE characters TO characters_temp");

        try {
            $this->expectException(RowDeletionFailedException::class);
            $this->repository->delete($character);

        } finally {
            $this->pdo->exec("RENAME TABLE characters_temp TO characters");
        }
    }

    /**
     * @test
     * @group unhappy-path
     * @group integration
     * @group character
     * @group character-repository
     */
    public function testRowInsertionFailsUsingExistingPdoConnection(): void
    {
        $character = new Character(
            'Test Character', 
            '2000-01-01', 
            'Test Kingdom', 
            1,
            1
        );

        $this->pdo->exec("RENAME TABLE characters TO characters_temp");

        try {
            $this->expectException(RowInsertionFailedException::class);
            $this->repository->save($character);

        } finally {
            $this->pdo->exec("RENAME TABLE characters_temp TO characters");
        }
    }

    /**
     * Test para comprobar que, si la operación UPDATE falla, se lanza la excepción RowUpdateFailedException.
     *
     * En este test se simula el fallo en la consulta UPDATE renombrando temporalmente la tabla "characters"
     * para provocar que la query en el método update (llamado a través de save para un Character con ID no nulo)
     * falle y se lance la excepción personalizada.
     *
     * @test
     * @group unhappy-path
     * @group integration
     * @group character
     * @group character-repository
     * @group ruyi
     */
    public function testRowUpdateFailsUsingExistingPdoConnection(): void
    {
        // Creamos un Character con un ID asignado para forzar la actualización (update)
        $character = new Character(
            'Test Character',
            '2000-01-01',
            'Test Kingdom',
            1,   // equipment_id (valor de prueba)
            1,   // faction_id (valor de prueba)
            100  // id: se asume que el Character ya existe (para que se invoque update en save)
        );

        // Renombramos temporalmente la tabla "characters" para que la query UPDATE falle
        $this->pdo->exec("RENAME TABLE characters TO characters_temp");

        try {
            // Esperamos que, al no existir la tabla "characters", la operación update falle y se lance la excepción RowUpdateFailedException
            $this->expectException(RowUpdateFailedException::class);

            // Como en el repositorio el método save detecta que el id no es nulo, se ejecuta la ruta de actualización
            $this->repository->save($character);
        } finally {
            // Restauramos el nombre original de la tabla para no afectar otros tests
            $this->pdo->exec("RENAME TABLE characters_temp TO characters");
        }
    }

    
}
