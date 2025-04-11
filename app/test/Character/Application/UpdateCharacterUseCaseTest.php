<?php

namespace App\Test\Character\Application;

use App\Character\Application\UpdateCharacterUseCase;
use App\Character\Application\UpdateCharacterUseCaseRequest;
use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Domain\Exception\CharacterNotFoundException;
use App\Character\Domain\Exception\CharacterValidationException;
use App\Character\Infrastructure\InMemory\ArrayCharacterRepository;
use PHPUnit\Framework\TestCase;

class UpdateCharacterUseCaseTest extends TestCase
{
    /**
     * @test
     * @group unit
     * @group character
     * @group character-unit
     * @group update-character
     * @group happy-path
     */
    public function givenARequestWithValidDataWhenUpdateCharacterThenReturnSuccess()
    {
        // Arrange
        $characterId = 1;
        $oldCharacter = new Character(
            name: 'Old Name',
            birth_date: '1990-01-01',
            kingdom: 'Old Kingdom',
            equipment_id: 1,
            faction_id: 1,
            id: $characterId
        );

        $repository = $this->mockCharacterRepository([$oldCharacter]);
        $sut = new UpdateCharacterUseCase($repository);

        $request = new UpdateCharacterUseCaseRequest(
            id: $characterId,
            name: 'New Name',
            birthDate: '1995-05-05',
            kingdom: 'New Kingdom',
            equipmentId: 2,
            factionId: 2
        );

        // Act
        $result = $sut->execute($request);

        // Assert
        $this->assertEquals($characterId, $result->getCharacter()->getId());
        $this->assertEquals('New Name', $result->getCharacter()->getName());
        $this->assertEquals('1995-05-05', $result->getCharacter()->getBirthDate());
        $this->assertEquals('New Kingdom', $result->getCharacter()->getKingdom());
        $this->assertEquals(2, $result->getCharacter()->getEquipmentId());
        $this->assertEquals(2, $result->getCharacter()->getFactionId());
    }

    private function mockCharacterRepository(array $characters): CharacterRepository
    {
        $repository = new ArrayCharacterRepository();

        foreach ($characters as $character) {
            $repository->save($character);
        }

        return $repository;
    }

    /**
     * @test
     * @group unit
     * @group character
     * @group character-unit
     * @group update-character
     * @group unhappy-path
     */
    public function givenARequestWithNonExistentIdWhenUpdateCharacterThenThrowException()
    {
        // Arrange
        $characterId = 999;
        $repository = $this->mockCharacterRepository([]);
        $sut = new UpdateCharacterUseCase($repository);

        $request = new UpdateCharacterUseCaseRequest(
            id: $characterId,
            name: 'New Name',
            birthDate: '1995-05-05',
            kingdom: 'New Kingdom',
            equipmentId: 2,
            factionId: 2
        );

        // Act & Assert
        $this->expectException(CharacterNotFoundException::class);
        $sut->execute($request);
    }

    /**
     * @test
     * @group unit
     * @group character
     * @group character-unit
     * @group update-character
     * @group validation
     */
    public function givenARequestWithInvalidDataWhenUpdateCharacterThenThrowException()
    {
        // Arrange
        $characterId = 1;
        $oldCharacter = new Character(
            name: 'Old Name',
            birth_date: '1990-01-01',
            kingdom: 'Old Kingdom',
            equipment_id: 1,
            faction_id: 1,
            id: $characterId
        );

        $repository = $this->mockCharacterRepository([$oldCharacter]);
        $sut = new UpdateCharacterUseCase($repository);

        $request = new UpdateCharacterUseCaseRequest(
            id: $characterId,
            name: '', // Invalid empty name
            birthDate: '1995-05-05',
            kingdom: 'New Kingdom',
            equipmentId: 2,
            factionId: 2
        );

        // Act & Assert
        $this->expectException(CharacterValidationException::class);
        $sut->execute($request);
    }

}
