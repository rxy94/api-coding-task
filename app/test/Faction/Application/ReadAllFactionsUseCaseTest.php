<?php

namespace App\Test\Faction\Application;

use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionRepository;
use App\Faction\Application\ReadFactionUseCase;
use App\Faction\Infrastructure\Persistence\InMemory\ArrayFactionRepository;

use PHPUnit\Framework\TestCase;

class ReadAllFactionsUseCaseTest extends TestCase
{
    private function mockFactionRepository(array $factions): FactionRepository
    {
        $repository = new ArrayFactionRepository([]);

        foreach ($factions as $faction) {
            $repository->save($faction);
        }

        return $repository;
    }

    /**
     * @test
     * @group happy-path
     * @group unit
     * @group faction
     * @group read-all-factions
     */
    public function givenARepositoryWithMultipleFactionsWhenReadAllFactionsThenReturnAllFactions()
    {
        $faction1 = new Faction(
            'Kingdom of Spain',
            'A powerful kingdom in the south of Europe',
            1
        );

        $faction2 = new Faction(
            'Kingdom of France',
            'A powerful kingdom in the north of Europe',
            2
        );

        $sut = new ReadFactionUseCase(
            $this->mockFactionRepository([$faction1, $faction2])
        );

        $result = $sut->execute();

        $this->assertCount(2, $result);
        $this->assertEquals($faction1, $result[1]);
        $this->assertEquals($faction2, $result[2]);
    }

    /**
     * @test
     * @group unhappy-path
     * @group unit
     * @group faction
     * @group read-all-factions
     */
    public function givenARepositoryWithNoFactionsWhenReadAllFactionsThenReturnEmptyArray()
    {
        $sut = new ReadFactionUseCase(
            $this->mockFactionRepository([])
        );

        $result = $sut->execute();

        $this->assertEquals([], $result);
    }
}