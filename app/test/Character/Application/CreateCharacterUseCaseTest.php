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
     * @group character
     * @group create-character
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
     * @group character
     * @group create-character
     * @group validation
     * @dataProvider invalidDataProvider
     */
    public function givenARequestWithInvalidDataWhenCreateCharacterThenReturnError(
        CreateCharacterUseCaseRequest $request,
        DomainException $expectedException, 
        string $expectedErrorMessage
    ) {
        $sut = new CreateCharacterUseCase(
            $this->mockCharacterRepository([])
        );

        $this->expectException($expectedException::class);
        $this->expectExceptionMessage($expectedErrorMessage);
        $sut->execute($request);
    }

    public static function invalidDataProvider(): array
    {
        return [
            'invalid name' => [
                CreateCharacterUseCaseRequestMotherObject::withInvalidName(),
                CharacterValidationException::withNameRequired(),
                CharacterValidationException::withNameRequired()->getMessage(),
            ],
            'invalid length name' => [
                CreateCharacterUseCaseRequestMotherObject::withInvalidLengthName(),
                CharacterValidationException::withNameLengthError(),
                CharacterValidationException::withNameLengthError()->getMessage(),
            ],
            'invalid birth date' => [
                CreateCharacterUseCaseRequestMotherObject::withInvalidBirthDate(),
                CharacterValidationException::withBirthDateRequired(),
                CharacterValidationException::withBirthDateRequired()->getMessage(),
            ],
            'invalid birth date format' => [
                CreateCharacterUseCaseRequestMotherObject::withInvalidFormatBirthDate(),
                CharacterValidationException::withBirthDateFormatError(),
                CharacterValidationException::withBirthDateFormatError()->getMessage(),
            ],
            'invalid kingdom' => [
                CreateCharacterUseCaseRequestMotherObject::withInvalidKingdom(),
                CharacterValidationException::withKingdomRequired(),
                CharacterValidationException::withKingdomRequired()->getMessage(),
            ],
            'invalid kingdom length' => [
                CreateCharacterUseCaseRequestMotherObject::withInvalidLengthKingdom(),
                CharacterValidationException::withKingdomLengthError(),
                CharacterValidationException::withKingdomLengthError()->getMessage(),
            ],
            'invalid equipment ID' => [
                CreateCharacterUseCaseRequestMotherObject::withRequiredEquipmentId(),
                CharacterValidationException::withEquipmentIdRequired(),
                CharacterValidationException::withEquipmentIdRequired()->getMessage(),
            ],
            'invalid equipment ID non positive' => [
                CreateCharacterUseCaseRequestMotherObject::withInvalidEquipmentId(),
                CharacterValidationException::withEquipmentIdNonPositive(),
                CharacterValidationException::withEquipmentIdNonPositive()->getMessage(),
            ],
            'invalid faction ID' => [
                CreateCharacterUseCaseRequestMotherObject::withRequiredFactionId(),
                CharacterValidationException::withFactionIdRequired(),
                CharacterValidationException::withFactionIdRequired()->getMessage(),
            ],
            'invalid faction ID non positive' => [
                CreateCharacterUseCaseRequestMotherObject::withInvalidFactionId(),
                CharacterValidationException::withFactionIdNonPositive(),
                CharacterValidationException::withFactionIdNonPositive()->getMessage(),
            ],
            'invalid ID' => [
                CreateCharacterUseCaseRequestMotherObject::withInvalidId(),
                CharacterValidationException::withIdNonPositive(),
                CharacterValidationException::withIdNonPositive()->getMessage(),
            ],
        ];
    }
}
