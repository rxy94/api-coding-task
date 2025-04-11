<?php

namespace App\Test\Character\Application;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Domain\Exception\CharacterNotFoundException;
use App\Character\Application\ReadCharacterByIdUseCase;
use App\Character\Infrastructure\InMemory\ArrayCharacterRepository;

use PHPUnit\Framework\TestCase;

class ReadCharacterUseCaseTest extends TestCase
{
    /**
     * @test
     * @group happy-path
     * @group unit
     * @group character
     * @group character-unit
     * @group read-character
     */
    public function givenARepositoryWithOneCharacterIdWhenReadCharacterThenReturnTheCharacter()
    {
        $sut = new ReadCharacterByIdUseCase(
            $this->mockCharacterRepository([
                new Character( //Se puede usar el characterFactory para crear un character
                    'John Doe',
                    '1990-01-01',
                    'Kingdom of Spain',
                    1,
                    1,
                    1,
                ),
            ]),
        );

        $character = $sut->execute(1);

        $this->assertEquals(1, $character->getId());
        $this->assertEquals('John Doe', $character->getName());
        $this->assertEquals('1990-01-01', $character->getBirthDate());
        $this->assertEquals('Kingdom of Spain', $character->getKingdom());
        $this->assertEquals(1, $character->getEquipmentId());
        $this->assertEquals(1, $character->getFactionId());
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
     * @group unhappy-path
     * @group unit
     * @group character
     * @group character-unit
     * @group read-character
     */
    public function givenARepositoryWithNonExistingCharacterIdWhenReadCharacterThenExceptionShouldBeRaised()
    {
        $sut = new ReadCharacterByIdUseCase(
            $this->mockCharacterRepository([]),
        );

        $this->expectException(CharacterNotFoundException::class);

        $sut->execute(1);
    }

}