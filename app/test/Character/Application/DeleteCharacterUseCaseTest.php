<?php

namespace App\Test\Character\Application;

use App\Character\Application\DeleteCharacterUseCase;
use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Domain\Exception\CharacterNotFoundException;
use App\Character\Domain\Exception\CharacterValidationException;
use App\Character\Infrastructure\InMemory\ArrayCharacterRepository;
use DomainException;
use PHPUnit\Framework\TestCase;
use App\Test\Character\Application\MotherObject\CreateCharacterUseCaseRequestMotherObject;

class DeleteCharacterUseCaseTest extends TestCase
{
    /**
     * @test
     * @group unit
     * @group character
     * @group delete-character
     * @group happy-path
     */
    public function givenARequestWithValidDataWhenDeleteCharacterThenReturnSuccess()
    {
        $repository = $this->mockCharacterRepository([
            new Character(
                'John Doe',
                '1990-01-01',
                'Kingdom of Spain',
                1,
                1,
                1,
            ),
        ]);

        $sut = new DeleteCharacterUseCase($repository);

        $sut->execute(1);
        
        # Verificamos que el personaje ha sido eliminado
        $this->assertNull($repository->findById(1));
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
     * @group delete-character
     * @group happy-path
     */
    public function givenMultipleCharactersWhenDeleteOneThenOnlyThatOneIsDeleted()
    {
        $character1 = new Character(
            'John Doe',
            '1990-01-01',
            'Kingdom of Spain',
            1,
            1,
            1,
        );
        
        $character2 = new Character(
            'Jane Doe',
            '1992-02-02',
            'Kingdom of Spain',
            2,
            2,
            2,
        );

        $repository = $this->mockCharacterRepository([$character1, $character2]);
        $sut = new DeleteCharacterUseCase($repository);

        $sut->execute(1);

        # Verificamos que el primer personaje fue eliminado
        $this->assertNull($repository->findById(1));
        # Verificamos que el segundo personaje sigue existiendo
        $this->assertNotNull($repository->findById(2));
    }

    /**
     * @test
     * @group unit
     * @group character
     * @group delete-character
     * @group unhappy-path
     */
    public function givenARequestWithNonExistentIdWhenDeleteCharacterThenThrowException()
    {
        $sut = new DeleteCharacterUseCase($this->mockCharacterRepository([]));
        
        $this->expectException(CharacterNotFoundException::class);

        $sut->execute(999);  
    }

}

