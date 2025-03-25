<?php

namespace App\Equipment\Infrastructure;

use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Domain\Equipment;
use PDO;

class MySQLEquipmentRepository implements EquipmentRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findById(int $id): ?Equipment
    {
        $sql = 'SELECT * FROM equipments WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return self::fromArray($data, $this->pdo);
    }

    private function fromArray(array $data): Equipment
    {
        $equipment = new Equipment(
            $data['name'],
            $data['type'],
            $data['made_by'],
            $data['id'] ?? null
        );

        return $equipment;
    }

    public function findAll(): array
    {
        try {
            $sql = 'SELECT * FROM equipments';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $equipments = [];

            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $equipments[] = self::fromArray($data);
            }

            return $equipments;

        } catch (\PDOException $e) {
            throw new \PDOException("Error al obtener los equipos: " . $e->getMessage());
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

        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            'name'    => $equipment->getName(),
            'type'    => $equipment->getType(),
            'made_by' => $equipment->getMadeBy(),
        ]);

        if ($result) {
            $id = (int) $this->pdo->lastInsertId();
            return new Equipment(
                $equipment->getName(),
                $equipment->getType(),
                $equipment->getMadeBy(),
                $id
            );
        }

        throw new \RuntimeException('Error al insertar el equipo');
    }

    private function update(Equipment $equipment): Equipment
    {
        $sql = 'UPDATE equipments 
                SET name = :name, 
                    type = :type, 
                    made_by = :made_by 
                WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id'      => $equipment->getId(),
            'name'    => $equipment->getName(),
            'type'    => $equipment->getType(),
            'made_by' => $equipment->getMadeBy(),
        ]);

        return $equipment;
    }

    public function delete(Equipment $equipment): bool
    {
        if (null !== $equipment->getId()) {
            return false;
        }

        $sql = 'DELETE FROM equipments WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $equipment->getId()]);
    }
    
}
