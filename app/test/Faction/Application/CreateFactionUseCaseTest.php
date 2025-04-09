<?php

namespace App\Test\Faction\Application;

use App\Faction\Domain\FactionRepository;
use App\Faction\Domain\Exception\FactionValidationException;
use App\Faction\Application\CreateFactionUseCase;
use App\Faction\Application\CreateFactionUseCaseRequest;
use App\Faction\Infrastructure\Persistence\InMemory\ArrayFactionRepository;
use DomainException;
use PHPUnit\Framework\TestCase;
use App\Test\Faction\Application\MotherObject\CreateFactionUseCaseRequestMotherObject;

class CreateFactionUseCaseTest extends TestCase
{
    /**
     * @test
     * @group happy-path
     * @group unit
     * @group faction
     * @group create-faction
     */
    public function givenARequestWithValidDataWhenCreateFactionThenReturnSuccess()
    {
        $request = CreateFactionUseCaseRequestMotherObject::valid();
        $sut = new CreateFactionUseCase(
            $this->mockFactionRepository([])
        );

        $result = $sut->execute($request);

        $this->assertNotNull($result->getFaction()->getId());
        $this->assertEquals(1, $result->getFaction()->getId());
        $this->assertGreaterThan(0, $result->getFaction()->getId());
        $this->assertEquals('Kingdom of Spain', $result->getFaction()->getName());
        $this->assertEquals('A powerful kingdom in the south of Europe', $result->getFaction()->getDescription());
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
     * @group create-faction
     * @group validation
     * @dataProvider invalidDataProvider
     */
    public function givenARequestWithInvalidDataWhenCreateFactionThenReturnError(
        CreateFactionUseCaseRequest $request,
        DomainException $expectedException, 
        string $expectedErrorMessage
    ) {
        $sut = new CreateFactionUseCase(
            $this->mockFactionRepository([])
        );

        $this->expectException($expectedException::class);
        $this->expectExceptionMessage($expectedErrorMessage);
        $sut->execute($request);
    }

    public static function invalidDataProvider(): array
    {
        return [
            'invalid name' => [
                CreateFactionUseCaseRequestMotherObject::withInvalidName(),
                FactionValidationException::withFactionNameError(),
                FactionValidationException::withFactionNameError()->getMessage(),
            ],
            'invalid length name' => [
                CreateFactionUseCaseRequestMotherObject::withInvalidLengthName(),
                FactionValidationException::withFactionNameLengthError(),
                FactionValidationException::withFactionNameLengthError()->getMessage(),
            ],
            'invalid description' => [
                CreateFactionUseCaseRequestMotherObject::withInvalidDescription(),
                FactionValidationException::withDescriptionError(),
                FactionValidationException::withDescriptionError()->getMessage(),
            ],
            'invalid length description' => [
                CreateFactionUseCaseRequestMotherObject::withInvalidLengthDescription(),
                FactionValidationException::withDescriptionLengthError(),
                FactionValidationException::withDescriptionLengthError()->getMessage(),
            ],
            'invalid ID' => [
                CreateFactionUseCaseRequestMotherObject::withInvalidId(),
                FactionValidationException::withIdNonPositive(),
                FactionValidationException::withIdNonPositive()->getMessage(),
            ],
        ];
    }
}
