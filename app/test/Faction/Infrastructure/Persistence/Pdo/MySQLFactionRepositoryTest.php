<?php

namespace App\Test\Faction\Infrastructure\Persistence\Pdo;

use App\Faction\Domain\Faction;
use App\Faction\Domain\Exception\FactionNotFoundException;
use App\Faction\Infrastructure\Persistence\Pdo\MySQLFactionRepository;
use App\Shared\Infrastructure\Persistence\Pdo\Exception\RowDeletionFailedException;
use App\Shared\Infrastructure\Persistence\Pdo\Exception\RowInsertionFailedException;
use App\Shared\Infrastructure\Persistence\Pdo\Exception\RowUpdateFailedException;
use PDO;
use PHPUnit\Framework\TestCase;

class MySQLFactionRepositoryTest extends TestCase
{
    private PDO $pdo;
    private MySQLFactionRepository $repository;
    private array $insertedFactionIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = $this->createPdoConnection();
        $this->repository = new MySQLFactionRepository($this->pdo);
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
     * @group faction
     * @group repository
     * @group faction-repository
     */
    public function givenARepositoryWithOneFactionIdWhenReadFactionThenReturnTheFaction()
    {
        $faction = new Faction(
            'Kingdom of Spain',
            'A powerful kingdom in the south of Europe'
        );

        $savedFaction = $this->repository->save($faction);
        $this->insertedFactionIds[] = $savedFaction->getId();
        $foundFaction = $this->repository->findById($savedFaction->getId());

        $this->assertEquals('Kingdom of Spain', $foundFaction->getName());
        $this->assertEquals('A powerful kingdom in the south of Europe', $foundFaction->getDescription());
    }

    /**
     * @test
     * @group happy-path
     * @group integration
     * @group faction
     * @group repository
     * @group faction-repository
     */
    public function givenARepositoryWhenCreateFactionThenFactionIsSaved()
    {
        $faction = new Faction(
            'Kingdom of Spain',
            'A powerful kingdom in the south of Europe',
        );

        $savedFaction = $this->repository->save($faction);
        $this->insertedFactionIds[] = $savedFaction->getId();

        $this->assertNotNull($savedFaction->getId());
        $this->assertEquals('Kingdom of Spain', $savedFaction->getName());
        $this->assertEquals('A powerful kingdom in the south of Europe', $savedFaction->getDescription());
    }

    /**
     * @test
     * @group happy-path
     * @group integration
     * @group faction
     * @group repository
     * @group faction-repository
     */
    public function givenARepositoryWithFactionWhenUpdateFactionThenFactionIsUpdated()
    {
        $faction = new Faction(
            'Kingdom of Spain',
            'A powerful kingdom in the south of Europe'
        );
        
        $savedFaction = $this->repository->save($faction);
        $this->insertedFactionIds[] = $savedFaction->getId();

        $this->assertNotNull($savedFaction->getId());

        $updatedFaction = new Faction(
            'Kingdom of France',
            'A powerful kingdom in the north of Europe',
            $savedFaction->getId()
        );

        $result = $this->repository->save($updatedFaction);
        $foundFaction = $this->repository->findById($result->getId());

        $this->assertEquals($savedFaction->getId(), $foundFaction->getId());
        $this->assertEquals('Kingdom of France', $foundFaction->getName());
        $this->assertEquals('A powerful kingdom in the north of Europe', $foundFaction->getDescription());
    }

    /**
     * @test
     * @group happy-path
     * @group integration
     * @group faction
     * @group repository
     * @group faction-repository
     */
    public function givenARepositoryWithMultipleFactionsWhenFindAllThenReturnAllFactions()
    {
        $faction1 = new Faction(
            'Kingdom of Spain',
            'A powerful kingdom in the south of Europe'
        );
        $faction2 = new Faction(
            'Kingdom of France',
            'A powerful kingdom in the north of Europe'
        );

        $savedFaction1 = $this->repository->save($faction1);
        $savedFaction2 = $this->repository->save($faction2);
        $this->insertedFactionIds[] = $savedFaction1->getId();
        $this->insertedFactionIds[] = $savedFaction2->getId();

        $factions = $this->repository->findAll();

        $this->assertCount(2, $factions);
        $this->assertEquals($savedFaction1->getId(), $factions[0]->getId());
        $this->assertEquals($savedFaction2->getId(), $factions[1]->getId());
    }

    /**
     * @test
     * @group unhappy-path
     * @group integration
     * @group faction
     * @group repository
     * @group faction-repository
     */
    public function givenARepositoryWithFactionWhenDeleteFactionThenFactionIsDeleted()
    {
        $faction = new Faction(
            'Kingdom of Spain',
            'A powerful kingdom in the south of Europe'
        );

        $savedFaction = $this->repository->save($faction);
        $this->insertedFactionIds[] = $savedFaction->getId();

        $result = $this->repository->delete($savedFaction);

        $this->assertTrue($result);
        $this->expectException(FactionNotFoundException::class);
        $this->repository->findById($savedFaction->getId());
    }

    /**
     * @test
     * @group unhappy-path
     * @group integration
     * @group faction
     * @group repository
     * @group faction-repository
     */
    public function givenARepositoryWhenFindByIdWithNonExistentIdThenThrowException()
    {
        $this->expectException(FactionNotFoundException::class);
        $this->repository->findById(999);
    }

    /**
     * @test
     * @group unhappy-path
     * @group integration
     * @group faction
     * @group faction-repository
     */
    public function testRowInsertionFailsUsingExistingPdoConnection(): void
    {
        $faction = new Faction(
            'Test Faction',
            'Test Description'
        );

        $this->pdo->exec("RENAME TABLE factions TO factions_temp");

        try {
            $this->expectException(RowInsertionFailedException::class);
            $this->repository->save($faction);
        } finally {
            $this->pdo->exec("RENAME TABLE factions_temp TO factions");
        }
    }

    /**
     * @test
     * @group unhappy-path
     * @group integration
     * @group faction
     * @group faction-repository
     */
    public function testRowUpdateFailsUsingExistingPdoConnection(): void
    {
        $faction = new Faction(
            'Test Faction',
            'Test Description',
            999
        );

        $this->pdo->exec("RENAME TABLE factions TO factions_temp");

        try {
            $this->expectException(RowUpdateFailedException::class);
            $this->repository->save($faction);
        } finally {
            $this->pdo->exec("RENAME TABLE factions_temp TO factions");
        }
    }

    /**
     * @test
     * @group unhappy-path
     * @group integration
     * @group faction
     * @group faction-repository
     */
    public function testRowDeletionFailsUsingExistingPdoConnection(): void
    {
        $faction = new Faction(
            'Test Faction',
            'Test Description',
            999
        );

        $this->pdo->exec("RENAME TABLE factions TO factions_temp");

        try {
            $this->expectException(RowDeletionFailedException::class);
            $this->repository->delete($faction);
        } finally {
            $this->pdo->exec("RENAME TABLE factions_temp TO factions");
        }
    }

}
