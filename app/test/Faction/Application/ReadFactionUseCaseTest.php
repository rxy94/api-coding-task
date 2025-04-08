<?php

namespace App\Test\Faction\Application;

use App\Faction\Domain\FactionRepository;
use App\Faction\Domain\Exception\FactionValidationException;
use App\Faction\Application\ReadFactionByIdUseCase;
use App\Faction\Domain\Faction;
use App\Faction\Infrastructure\Persistence\InMemory\ArrayFactionRepository;
use App\Faction\Infrastructure\Persistence\Pdo\Exception\FactionNotFoundException;
use DomainException;
use PHPUnit\Framework\TestCase;
use App\Test\Faction\Application\MotherObject\CreateFactionUseCaseRequestMotherObject;


class ReadFactionUseCaseTest extends TestCase
{
    /**
     * @test
     * @group happy-path
     * @group unit
     * @group faction
     * @group read-faction
     */
    public function givenARepositoryWithOneFactionIdWhenReadFactionThenReturnTheFaction()
    {
        $sut = new ReadFactionByIdUseCase(
            $this->mockFactionRepository([
                new Faction(
                    'Kingdom of Spain',
                    'A powerful kingdom in the south of Europe',
                    1
                ),
            ]),
        );

        $faction = $sut->execute(1);

        $this->assertEquals(1, $faction->getId());
        $this->assertEquals('Kingdom of Spain', $faction->getName());
        $this->assertEquals('A powerful kingdom in the south of Europe', $faction->getDescription());
    }

    private function mockFactionRepository(array $factions): FactionRepository
    {
        $repository = new ArrayFactionRepository();

        foreach ($factions as $faction) {
            $repository->save($faction);
        }

        return $repository;
    }

    /**
     * @test
     * @group unhappy-path
     * @group unit
     * @group faction
     * @group read-faction
     */
    public function givenARepositoryWithNonExistingFactionIdWhenReadFactionThenExceptionShouldBeRaised()
    {
        $sut = new ReadFactionByIdUseCase(
            $this->mockFactionRepository([]),
        );

        $this->expectException(FactionNotFoundException::class);

        $sut->execute(1);
    }
    
    
}