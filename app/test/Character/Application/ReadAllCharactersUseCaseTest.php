<?php

namespace App\Test\Character\Application;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Application\ReadCharacterUseCase;
use App\Character\Infrastructure\InMemory\ArrayCharacterRepository;

use PHPUnit\Framework\TestCase;

class ReadAllCharactersUseCaseTest extends TestCase
{
    private function mockCharacterRepository(array $characters): CharacterRepository
    {
        $repository = new ArrayCharacterRepository([]);

        foreach ($characters as $character) {
            $repository->save($character);
        }

        return $repository;
    }

    /**
     * @test
     * @group happy-path
     * @group unit
     * @group character
     * @group read-all-characters
     */
    public function givenARepositoryWithMultipleCharactersWhenReadAllCharactersThenReturnAllCharacters()
    {
        $character1 = new Character(
            'John Doe',
            '1990-01-01',
            'Kingdom of Spain',
            1,
            1,
            1
        );

        $character2 = new Character(
            'Jane Doe',
            '1990-01-01',
            'Kingdom of Spain',
            2,
            2,
            2
        );

        $sut = new ReadCharacterUseCase(
            $this->mockCharacterRepository([$character1, $character2])
        );

        $result = $sut->execute();

        $this->assertCount(2, $result);
        $this->assertEquals($character1, $result[1]);
        $this->assertEquals($character2, $result[2]);
    }

    /**
     * @test
     * @group unhappy-path
     * @group unit
     * @group character
     * @group read-all-characters
     */
    public function givenARepositoryWithNoCharactersWhenReadAllCharactersThenReturnEmptyArray()
    {
        $sut = new ReadCharacterUseCase(
            $this->mockCharacterRepository([])
        );

        $result = $sut->execute();

        $this->assertEquals([], $result);
    }
}