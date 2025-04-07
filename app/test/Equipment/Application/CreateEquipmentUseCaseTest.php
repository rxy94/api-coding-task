<?php

namespace App\Test\Equipment\Application;

use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Domain\Exception\EquipmentValidationException;
use App\Equipment\Application\CreateEquipmentUseCase;
use App\Equipment\Application\CreateEquipmentUseCaseRequest;
use App\Equipment\Infrastructure\InMemory\ArrayEquipmentRepository;
use DomainException;
use PHPUnit\Framework\TestCase;
use App\Test\Equipment\Application\MotherObject\CreateEquipmentUseCaseRequestMotherObject;

class CreateEquipmentUseCaseTest extends TestCase
{
    /**
     * @test
     * @group happy-path
     * @group unit
     * @group equipment
     * @group create-equipment
     */
    public function givenARequestWithValidDataWhenCreateEquipmentThenReturnSuccess()
    {
        $request = CreateEquipmentUseCaseRequestMotherObject::valid();
        $sut = new CreateEquipmentUseCase(
            $this->mockEquipmentRepository([])
        );

        $result = $sut->execute($request);

        $this->assertNotNull($result->getEquipment()->getId());
        $this->assertEquals(1, $result->getEquipment()->getId());
        $this->assertGreaterThan(0, $result->getEquipment()->getId());
        $this->assertEquals('Sword of Destiny', $result->getEquipment()->getName());
        $this->assertEquals('A legendary sword', $result->getEquipment()->getType());
        $this->assertEquals('John Doe', $result->getEquipment()->getMadeBy());
    }

    private function mockEquipmentRepository(array $equipments): EquipmentRepository
    {
        $repository = new ArrayEquipmentRepository();

        foreach ($equipments as $equipment) {
            $repository->save($equipment);
        }

        return $repository;
    }

    /**
     * @test
     * @group unhappy-path
     * @group unit
     * @group equipment
     * @group validation
     * @dataProvider invalidDataProvider
     */
    public function givenARequestWithInvalidDataWhenCreateEquipmentThenReturnError(
        CreateEquipmentUseCaseRequest $request,
        DomainException $expectedException, 
        string $expectedErrorMessage
    ) {
        $sut = new CreateEquipmentUseCase(
            $this->mockEquipmentRepository([])
        );

        $this->expectException($expectedException::class);
        $this->expectExceptionMessage($expectedErrorMessage);
        $sut->execute($request);
    }

    public static function invalidDataProvider(): array
    {
        return [
            'invalid name' => [
                CreateEquipmentUseCaseRequestMotherObject::withInvalidName(),
                EquipmentValidationException::withNameError(),
                EquipmentValidationException::withNameError()->getMessage(),
            ],
            'invalid length name' => [
                CreateEquipmentUseCaseRequestMotherObject::withInvalidLengthName(),
                EquipmentValidationException::withNameLengthError(),
                EquipmentValidationException::withNameLengthError()->getMessage(),
            ],
            'invalid type' => [
                CreateEquipmentUseCaseRequestMotherObject::withInvalidType(),
                EquipmentValidationException::withTypeError(),
                EquipmentValidationException::withTypeError()->getMessage(),
            ],
            'invalid length type' => [
                CreateEquipmentUseCaseRequestMotherObject::withInvalidLengthType(),
                EquipmentValidationException::withTypeErrorLengthError(),
                EquipmentValidationException::withTypeErrorLengthError()->getMessage(),
            ],
            'invalid made by' => [
                CreateEquipmentUseCaseRequestMotherObject::withInvalidMadeBy(),
                EquipmentValidationException::withMadeByError(),
                EquipmentValidationException::withMadeByError()->getMessage(),
            ],
            'invalid length made by' => [
                CreateEquipmentUseCaseRequestMotherObject::withInvalidLengthMadeBy(),
                EquipmentValidationException::withMadeByLengthError(),
                EquipmentValidationException::withMadeByLengthError()->getMessage(),
            ],
            'invalid ID' => [
                CreateEquipmentUseCaseRequestMotherObject::withInvalidId(),
                EquipmentValidationException::withIdNonPositive(),
                EquipmentValidationException::withIdNonPositive()->getMessage(),
            ],
        ];
    }
} 