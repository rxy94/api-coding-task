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

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = $this->createPdoConnection();
        $this->repository = new MySQLCharacterRepository($this->pdo);
        
        // Limpiar la tabla antes de cada test
        $this->pdo->exec('DELETE FROM characters');
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
        // Arrange
        $character = new Character(
            'John Doe',
            '1990-01-01',
            'Kingdom of Spain',
            1,
            1,
        );

        // Act
        $savedCharacter = $this->repository->save($character);
        $foundCharacter = $this->repository->findById($savedCharacter->getId());

        // Assert
        $this->assertEquals('John Doe', $foundCharacter->getName());
        $this->assertEquals('1990-01-01', $foundCharacter->getBirthDate());
        $this->assertEquals('Kingdom of Spain', $foundCharacter->getKingdom());
        $this->assertEquals(1, $foundCharacter->getEquipmentId());
        $this->assertEquals(1, $foundCharacter->getFactionId());
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
            1,
            1,
        );

        // Act
        $savedCharacter = $this->repository->save($character);

        // Assert
        $this->assertNotNull($savedCharacter->getId());
        $this->assertEquals('Jane Doe', $savedCharacter->getName());
        $this->assertEquals('1992-02-02', $savedCharacter->getBirthDate());
        $this->assertEquals('Kingdom of France', $savedCharacter->getKingdom());
        $this->assertEquals(1, $savedCharacter->getEquipmentId());
        $this->assertEquals(1, $savedCharacter->getFactionId());
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
            1,
            1,
        );
        $savedCharacter = $this->repository->save($character);
        
        $updatedCharacter = new Character(
            'John Updated',
            '1995-05-05',
            'Kingdom of Portugal',
            1,
            1,
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
        $this->assertEquals(1, $foundCharacter->getEquipmentId());
        $this->assertEquals(1, $foundCharacter->getFactionId());
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
            1,
            1,
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
            1,
            1,
        );
        $character2 = new Character(
            'Jane Doe',
            '1992-02-02',
            'Kingdom of France',
            1,
            1,
        );
        
        $this->repository->save($character1);
        $this->repository->save($character2);

        // Act
        $characters = $this->repository->findAll();

        // Assert
        $this->assertCount(2, $characters);
        $this->assertEquals('John Doe', $characters[0]->getName());
        $this->assertEquals('Jane Doe', $characters[1]->getName());

    }

    private function createPdoConnection(): PDO
    {
        return new PDO('mysql:host=db;dbname=test', 'root', 'root');
    }
    
}
