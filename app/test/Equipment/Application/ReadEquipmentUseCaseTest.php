<?php

namespace App\Test\Equipment\Application;

use App\Equipment\Application\ReadEquipmentByIdUseCase;
use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Infrastructure\InMemory\ArrayEquipmentRepository;
use App\Equipment\Domain\Exception\EquipmentNotFoundException;
use PHPUnit\Framework\TestCase;

class ReadEquipmentUseCaseTest extends TestCase
{
    /**
     * @test
     * @group happy-path
     * @group unit
     * @group equipment
     * @group read-equipment
     */
    public function givenARepositoryWithOneEquipmentIdWhenReadEquipmentThenReturnTheEquipment()
    {
        $sut = new ReadEquipmentByIdUseCase(
            $this->mockEquipmentRepository([
                new Equipment(
                    'Sword of the King',
                    'A sword with a hilt of gold and a blade of steel',
                    'John Doe',
                    1
                ),
            ]),
        );

        $equipment = $sut->execute(1);

        $this->assertEquals(1, $equipment->getId());
        $this->assertEquals('Sword of the King', $equipment->getName());
        $this->assertEquals('A sword with a hilt of gold and a blade of steel', $equipment->getType());
        $this->assertEquals('John Doe', $equipment->getMadeBy());
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
     * @group read-equipment
     */
    public function givenARepositoryWithNonExistingEquipmentIdWhenReadEquipmentThenExceptionShouldBeRaised()
    {
        $sut = new ReadEquipmentByIdUseCase(
            $this->mockEquipmentRepository([]),
        );

        $this->expectException(EquipmentNotFoundException::class);

        $sut->execute(1);
    }

}