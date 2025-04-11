<?php

namespace App\Test\Faction\Application;

use App\Faction\Application\DeleteFactionUseCase;
use App\Faction\Domain\FactionRepository;
use App\Faction\Domain\Faction;
use App\Faction\Domain\Exception\FactionNotFoundException;
use App\Faction\Infrastructure\InMemory\ArrayFactionRepository;
use PHPUnit\Framework\TestCase;


class DeleteFactionUseCaseTest extends TestCase
{
    /**
     * @test
     * @group happy-path
     * @group unit
     * @group faction
     * @group delete-faction
     */
    public function givenARequestWithValidDataWhenDeleteFactionThenReturnSuccess()
    {
        $repository = $this->mockFactionRepository([
            new Faction(
                'Kingdom of Spain',
                'A powerful kingdom in the south of Europe',
                1
            ),
        ]);

        $sut = new DeleteFactionUseCase($repository);

        $sut->execute(1);

        $this->assertNull($repository->findById(1));
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
     * @group happy-path
     * @group unit
     * @group faction
     * @group delete-faction
     */
    public function givenMultipleFactionsWhenDeleteOneThenOnlyThatOneIsDeleted()
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

        $repository = $this->mockFactionRepository([$faction1, $faction2]);
        $sut = new DeleteFactionUseCase($repository);

        $sut->execute(1);

        $this->assertNull($repository->findById(1));
        $this->assertNotNull($repository->findById(2));
    }

    /**
     * @test
     * @group unhappy-path
     * @group unit
     * @group faction
     * @group delete-faction
     */
    public function givenARepositoryWithNonExistingFactionIdWhenDeleteFactionThenExceptionShouldBeRaised()
    {
        $sut = new DeleteFactionUseCase($this->mockFactionRepository([]));

        $this->expectException(FactionNotFoundException::class);

        $sut->execute(1);
    }
}