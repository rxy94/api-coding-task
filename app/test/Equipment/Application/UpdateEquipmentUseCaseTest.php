<?php

namespace App\Test\Equipment\Application;

use App\Equipment\Application\UpdateEquipmentUseCase;
use App\Equipment\Application\UpdateEquipmentUseCaseRequest;
use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Domain\Exception\EquipmentValidationException;
use App\Equipment\Infrastructure\InMemory\ArrayEquipmentRepository;
use App\Equipment\Infrastructure\Persistence\Pdo\Exception\EquipmentNotFoundException;
use PHPUnit\Framework\TestCase;

class UpdateEquipmentUseCaseTest extends TestCase
{
    /**
     * @test
     * @group unit
     * @group equipment
     * @group update-equipment
     * @group happy-path
     */
    public function givenARequestWithValidDataWhenUpdateEquipmentThenReturnSuccess()
    {
        $equipmentId = 1;
        $oldEquipment = new Equipment(
            name: 'Old Name',
            type: 'Old Type',
            made_by: 'Old Made By',
            id: $equipmentId
        );

        $repository = $this->mockEquipmentRepository([$oldEquipment]);
        $sut = new UpdateEquipmentUseCase($repository);

        $request = new UpdateEquipmentUseCaseRequest(
            id: $equipmentId,
            name: 'New Name',
            type: 'New Type',
            madeBy: 'New Made By'
        );

        $result = $sut->execute($request);
        $updatedEquipment = $result->getEquipment();

        $this->assertEquals($equipmentId, $updatedEquipment->getId());
        $this->assertEquals('New Name', $updatedEquipment->getName());
        $this->assertEquals('New Type', $updatedEquipment->getType());
        $this->assertEquals('New Made By', $updatedEquipment->getMadeBy());
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
     * @group unit
     * @group equipment
     * @group update-equipment
     * @group unhappy-path
     */
    public function givenARequestWithNonExistentIdWhenUpdateEquipmentThenThrowException()
    {
        $equipmentId = 999;
        $repository = $this->mockEquipmentRepository([]);
        $sut = new UpdateEquipmentUseCase($repository);

        $request = new UpdateEquipmentUseCaseRequest(
            id: $equipmentId,
            name: 'New Name',
            type: 'New Type',
            madeBy: 'New Made By'
        );

        $this->expectException(EquipmentNotFoundException::class);
        $sut->execute($request);
    }

    /**
     * @test
     * @group unit
     * @group equipment
     * @group update-equipment
     * @group unhappy-path
     */
    public function givenARequestWithInvalidDataWhenUpdateEquipmentThenThrowException()
    {
        $equipmentId = 1;
        $oldEquipment = new Equipment(
            name: 'Old Name',
            type: 'Old Type',
            made_by: 'Old Made By',
            id: $equipmentId
        );

        $repository = $this->mockEquipmentRepository([$oldEquipment]);
        $sut = new UpdateEquipmentUseCase($repository);

        $request = new UpdateEquipmentUseCaseRequest(
            id: $equipmentId,
            name: '',
            type: 'New Type',
            madeBy: 'New Made By'
        );

        $this->expectException(EquipmentValidationException::class);
        $sut->execute($request);
    }
    
}