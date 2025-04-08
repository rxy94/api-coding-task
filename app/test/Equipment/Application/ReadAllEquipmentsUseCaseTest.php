<?php

namespace App\Test\Equipment\Application;

use App\Equipment\Domain\Equipment;
use App\Equipment\Application\ReadEquipmentUseCase;
use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Infrastructure\InMemory\ArrayEquipmentRepository;

use PHPUnit\Framework\TestCase;

class ReadAllEquipmentsUseCaseTest extends TestCase
{
    private function mockEquipmentRepository(array $equipments): EquipmentRepository
    {
        $repository = new ArrayEquipmentRepository([]);

        foreach ($equipments as $equipment) {
            $repository->save($equipment);
        }

        return $repository;
    }

    /**
     * @test
     * @group happy-path
     * @group unit
     * @group equipment
     * @group read-all-equipments
     */
    public function givenARepositoryWithMultipleEquipmentsWhenReadAllEquipmentsThenReturnAllEquipments()
    {
        $equipment1 = new Equipment(
            'Sword',
            'Weapon',
            'Steel',
            1
        );

        $equipment2 = new Equipment(
            'Shield',
            'Armor',
            'Wood',
            2
        );

        $sut = new ReadEquipmentUseCase(
            $this->mockEquipmentRepository([$equipment1, $equipment2])
        );

        $result = $sut->execute();

        $this->assertCount(2, $result);
        $this->assertEquals($equipment1, $result[1]);
        $this->assertEquals($equipment2, $result[2]);
    }

    /**
     * @test
     * @group unhappy-path
     * @group unit
     * @group equipment
     * @group read-all-equipments
     */
    public function givenARepositoryWithNoEquipmentsWhenReadAllEquipmentsThenReturnEmptyArray()
    {
        $sut = new ReadEquipmentUseCase(
            $this->mockEquipmentRepository([])
        );

        $result = $sut->execute();

        $this->assertEquals([], $result);
    }
}

