<?php

namespace App\Equipment\Infrastructure\Persistence\Pdo;

use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Domain\Exception\EquipmentNotFoundException;
use App\Shared\Infrastructure\Persistence\Pdo\Exception\RowDeletionFailedException;
use App\Shared\Infrastructure\Persistence\Pdo\Exception\RowInsertionFailedException;
use App\Shared\Infrastructure\Persistence\Pdo\Exception\RowUpdateFailedException;
use PDO;

/**
 * Maneja las operaciones directas con la base de datos
 */
class MySQLEquipmentRepository implements EquipmentRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function findById(int $id): ?Equipment
    {
        $sql = 'SELECT * FROM equipments WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            throw EquipmentNotFoundException::build();
        }

        return MySQLEquipmentFactory::buildFromArray($data);
    }

    public function findAll(): array
    {
        try {
            $sql = 'SELECT * FROM equipments';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $equipments = [];

            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $equipments[] = MySQLEquipmentFactory::buildFromArray($data);
            }

            return $equipments;

        } catch (\PDOException $e) {
            throw EquipmentNotFoundException::build();
        }
    }

    public function save(Equipment $equipment): Equipment
    {
        if ($equipment->getId() === null) {
            return $this->insert($equipment);
        }

        return $this->update($equipment);
    }

    private function insert(Equipment $equipment): Equipment
    {
        $sql = 'INSERT INTO equipments (name, type, made_by)    
                VALUES (:name, :type, :made_by)';

        try {
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute(
                MySQLEquipmentToArrayTransformer::transform($equipment)
            );
        } catch (\PDOException $e) {
            throw RowInsertionFailedException::build();
        }

        if (!$result) {
            throw RowInsertionFailedException::build();
        }

        return MySQLEquipmentFactory::buildFromArray([
            'id'      => $this->pdo->lastInsertId(),
            'name'    => $equipment->getName(),
            'type'    => $equipment->getType(),
            'made_by' => $equipment->getMadeBy(),
        ]);
    }

    private function update(Equipment $equipment): Equipment
    {
        $sql = 'UPDATE equipments 
                SET name = :name, 
                    type = :type, 
                    made_by = :made_by 
                WHERE id = :id';

        try {
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute(
                MySQLEquipmentToArrayTransformer::transform($equipment)
            );
        } catch (\PDOException $e) {
            throw RowUpdateFailedException::build();
        }

        if (!$result) {
            throw RowUpdateFailedException::build();
        }

        return $equipment;
    }

    public function delete(Equipment $equipment): bool
    {
        if (null === $equipment->getId()) {
            throw EquipmentNotFoundException::build();
        }

        $sql = 'DELETE FROM equipments WHERE id = :id';

        try {
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute(['id' => $equipment->getId()]);
        } catch (\PDOException $e) {
            throw RowDeletionFailedException::build();
        }

        if (!$result) {
            throw RowDeletionFailedException::build();
        }

        return $result;

    }
    
}
