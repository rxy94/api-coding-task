<?php

namespace App\Test\Equipment\Application;

use App\Equipment\Application\DeleteEquipmentUseCase;
use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Infrastructure\InMemory\ArrayEquipmentRepository;
use App\Equipment\Domain\Exception\EquipmentNotFoundException;
use PHPUnit\Framework\TestCase;


class DeleteEquipmentUseCaseTest extends TestCase
{
    /**
     * @test
     * @group happy-path
     * @group unit
     * @group equipment
     * @group delete-equipment
     */
    public function givenARequestWithValidDataWhenDeleteEquipmentThenReturnSuccess()
    {
        $repository = $this->mockEquipmentRepository([
            new Equipment(
                'Sword of the King',
                'A sword with a hilt of gold and a blade of steel',
                'John Doe',
                1
            ),
        ]);

        $sut = new DeleteEquipmentUseCase($repository);

        $sut->execute(1);

        $this->assertNull($repository->findById(1));
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
     * @group happy-path
     * @group unit
     * @group equipment
     * @group delete-equipment
     */
    public function givenMultipleEquipmentsWhenDeleteOneThenOnlyThatOneIsDeleted()
    {
        $equipment1 = new Equipment(
            'Sword of the King',
            'A sword with a hilt of gold and a blade of steel',
            'John Doe',
            1
        );

        $equipment2 = new Equipment(
            'Shield of the King',
            'A shield with a hilt of gold and a blade of steel',
            'John Doe',
            2
        );

        $repository = $this->mockEquipmentRepository([$equipment1, $equipment2]);
        $sut = new DeleteEquipmentUseCase($repository);
        
        $sut->execute(1);

        $this->assertNull($repository->findById(1));
        $this->assertNotNull($repository->findById(2));
    }

    /**
     * @test
     * @group unhappy-path
     * @group unit
     * @group equipment
     * @group delete-equipment
     */
    public function givenARepositoryWithNonExistingEquipmentIdWhenDeleteEquipmentThenExceptionShouldBeRaised()
    {
        $sut = new DeleteEquipmentUseCase($this->mockEquipmentRepository([]));

        $this->expectException(EquipmentNotFoundException::class);

        $sut->execute(1);
    }
}