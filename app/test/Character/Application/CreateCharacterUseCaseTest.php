<?php

namespace App\Test\Character\Application;

use App\Character\Domain\CharacterRepository;
use App\Character\Domain\Exception\CharacterValidationException;
use App\Character\Application\CreateCharacterUseCase;
use App\Character\Application\CreateCharacterUseCaseRequest;
use App\Character\Infrastructure\InMemory\ArrayCharacterRepository;
use DomainException;
use PHPUnit\Framework\TestCase;
use App\Test\Character\Application\MotherObject\CreateCharacterUseCaseRequestMotherObject;

class CreateCharacterUseCaseTest extends TestCase
{
    /**
     * @test
     * @group happy-path
     * @group unit
     * @group ruyi
     */
    public function givenARequestWithValidDataWhenCreateCharacterThenReturnSuccess()
    {
        $request = CreateCharacterUseCaseRequestMotherObject::valid();
        $sut = new CreateCharacterUseCase(
            $this->mockCharacterRepository([])
        );

        $result = $sut->execute($request);

        $this->assertEquals(1, $result->getCharacter()->getId());
        $this->assertEquals('John Doe', $result->getCharacter()->getName());
        $this->assertEquals('1990-01-01', $result->getCharacter()->getBirthDate());
        $this->assertEquals('Kingdom of Spain', $result->getCharacter()->getKingdom());
        $this->assertEquals(1, $result->getCharacter()->getEquipmentId());
        $this->assertEquals(1, $result->getCharacter()->getFactionId());
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
     * @group ruyi
     * @dataProvider invalidDataProvider
     */
    public function givenARequestWithInvalidDataWhenCreateCharacterThenReturnError(
        CreateCharacterUseCaseRequest $request,
        DomainException $expectedException
    ) {
        $sut = new CreateCharacterUseCase(
            $this->mockCharacterRepository([])
        );

        $this->expectException($expectedException::class);
        $sut->execute($request);
    }

    public static function invalidDataProvider(): array
    {
        return [
            'invalid name' => [
                CreateCharacterUseCaseRequestMotherObject::withInvalidName(),
                CharacterValidationException::withNameRequired(),
            ],
            'invalid length name' => [
                CreateCharacterUseCaseRequestMotherObject::withInvalidLengthName(),
                CharacterValidationException::withNameLengthError(),
            ],
            'invalid birth date' => [
                CreateCharacterUseCaseRequestMotherObject::withInvalidBirthDate(),
                CharacterValidationException::withBirthDateRequired(),
            ],
            'invalid birth date format' => [
                CreateCharacterUseCaseRequestMotherObject::withInvalidFormatBirthDate(),
                CharacterValidationException::withBirthDateFormatError(),
            ],
            'invalid kingdom' => [
                CreateCharacterUseCaseRequestMotherObject::withInvalidKingdom(),
                CharacterValidationException::withKingdomRequired(),
            ],
            'invalid kingdom length' => [
                CreateCharacterUseCaseRequestMotherObject::withInvalidLengthKingdom(),
                CharacterValidationException::withKingdomLengthError(),
            ],
            'invalid equipment ID' => [
                CreateCharacterUseCaseRequestMotherObject::withRequiredEquipmentId(),
                CharacterValidationException::withEquipmentIdRequired(),
            ],
            'invalid equipment ID non positive' => [
                CreateCharacterUseCaseRequestMotherObject::withInvalidEquipmentId(),
                CharacterValidationException::withEquipmentIdNonPositive(),
            ],
            'invalid faction ID' => [
                CreateCharacterUseCaseRequestMotherObject::withRequiredFactionId(),
                CharacterValidationException::withFactionIdRequired(),
            ],
            'invalid faction ID non positive' => [
                CreateCharacterUseCaseRequestMotherObject::withInvalidFactionId(),
                CharacterValidationException::withFactionIdNonPositive(),
            ],
            'invalid ID' => [
                CreateCharacterUseCaseRequestMotherObject::withInvalidId(),
                CharacterValidationException::withIdNonPositive(),
            ],
        ];
    }
}
