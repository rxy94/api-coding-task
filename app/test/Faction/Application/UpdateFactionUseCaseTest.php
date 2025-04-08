<?php

namespace App\Test\Faction\Application;

use App\Faction\Application\UpdateFactionUseCase;
use App\Faction\Application\UpdateFactionUseCaseRequest;
use App\Faction\Domain\Exception\FactionValidationException;
use App\Faction\Domain\FactionRepository;
use App\Faction\Domain\Faction;
use App\Faction\Infrastructure\Persistence\InMemory\ArrayFactionRepository;
use App\Faction\Infrastructure\Persistence\Pdo\Exception\FactionNotFoundException;
use PHPUnit\Framework\TestCase;

class UpdateFactionUseCaseTest extends TestCase
{
    /**
     * @test
     * @group unit
     * @group faction
     * @group update-faction
     */
    public function givenARequestWithValidDataWhenUpdateFactionThenReturnSuccess()
    {
        $factionId = 1;
        $oldFaction = new Faction(
            'Kingdom of Spain',
            'A powerful kingdom in the south of Europe',
            $factionId
        );

        $repository = $this->mockFactionRepository([$oldFaction]);
        $sut = new UpdateFactionUseCase($repository);
        
        $request = new UpdateFactionUseCaseRequest(
            id: $factionId,
            faction_name: 'New Name',
            description: 'New Description'
        );
        
        $result = $sut->execute($request);
        $updatedFaction = $result->getFaction();

        $this->assertEquals($factionId, $updatedFaction->getId());
        $this->assertEquals('New Name', $updatedFaction->getName());
        $this->assertEquals('New Description', $updatedFaction->getDescription());
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
     * @group update-faction
     */
    public function givenARequestWithNonExistentIdWhenUpdateFactionThenThrowException()
    {
        $factionId = 999;
        $repository = $this->mockFactionRepository([]);
        $sut = new UpdateFactionUseCase($repository);

        $request = new UpdateFactionUseCaseRequest(
            id: $factionId,
            faction_name: 'New Name',
            description: 'New Description'
        );
     
        $this->expectException(FactionNotFoundException::class);
        $sut->execute($request);
    }

    /**
     * @test
     * @group unhappy-path
     * @group unit
     * @group faction
     * @group update-faction
     */
    public function givenARequestWithInvalidDataWhenUpdateFactionThenThrowException()
    {
        $factionId = 1;
        $oldFaction = new Faction(
            'Kingdom of Spain',
            'A powerful kingdom in the south of Europe',
            $factionId
        );

        $repository = $this->mockFactionRepository([$oldFaction]);
        $sut = new UpdateFactionUseCase($repository);
        
        $request = new UpdateFactionUseCaseRequest(
            id: $factionId,
            faction_name: '',
            description: 'New Description'
        );
        
        $this->expectException(FactionValidationException::class);
        $sut->execute($request);
    }
    
}