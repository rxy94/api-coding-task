<?php

namespace App\Model;

use PDO;
use PDOException;

class Equipment {
    private ?int $id = null;
    private string $name;
    private string $type;
    private string $made_by;

    public function __construct(private PDO $pdo) 
    {
    }

    # Getters and Setters
    public function setId(?int $id): self {
        $this->id = $id;
        return $this;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function setName(string $name): self {
        $this->name = $name;
        return $this;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setType(string $type): self {
        $this->type = $type;
        return $this;
    }

    public function getType(): string {
        return $this->type;
    }

    public function setMadeBy(string $made_by): self {
        $this->made_by = $made_by;
        return $this;
    }

    public function getMadeBy(): string {
        return $this->made_by;
    }

    /**
     *  Guarda el equipamiento en la base de datos
     *
     * @return boolean
     */
    public function save(): bool {
        try {
            if ($this->id === null) {
                // Insert
                $sql = "INSERT INTO equipments (name, type, made_by) 
                        VALUES (:name, :type, :made_by)";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    ':name' => $this->name,
                    ':type' => $this->type,
                    ':made_by' => $this->made_by
                ]);

                $this->id = (int) $this->pdo->lastInsertId();

            } else {
                // Update
                $sql = "UPDATE equipments 
                        SET name = :name, 
                            type = :type,
                            made_by = :made_by
                        WHERE id = :id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    ':id' => $this->id,
                    ':name' => $this->name,
                    ':type' => $this->type,
                    ':made_by' => $this->made_by
                ]);
            }

            return true;

        } catch (PDOException $e) {
            throw new PDOException("Error al guardar el equipamiento: " . $e->getMessage());
        }
    }

    /**
     *  Elimina el equipamiento de la base de datos
     *
     * @return boolean
     */
    public function delete(): bool {
        try {
            if ($this->id === null) {
                throw new PDOException("No se puede eliminar un equipamiento sin ID");
            }

            $sql = "DELETE FROM equipments WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $this->id]);
            return true;

        } catch (PDOException $e) {
            throw new PDOException("Error al eliminar el equipamiento: " . $e->getMessage());
        }
    }

    /**
     *  Busca el equipamiento por su id
     *
     * @param int $id
     * @return self|null
     */
    public function findById(int $id): ?self {
        try {
            $sql = "SELECT * FROM equipments WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                return null;
            }

            return $this->fromArray($data);

        } catch (PDOException $e) {
            throw new PDOException("Error al buscar el equipamiento: " . $e->getMessage());
        }
    }

    /**
     *  Busca todos los equipamientos
     *
     * @return array
     */
    public function findAll(): array {
        try {
            $sql = "SELECT * FROM equipments";
            $stmt = $this->pdo->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $equipments = [];
            foreach ($data as $row) {
                $equipment = new self($this->pdo);
                $equipments[] = $equipment->fromArray($row);
            }

            return $equipments;

        } catch (PDOException $e) {
            throw new PDOException("Error al buscar los equipamientos: " . $e->getMessage());
        }
    }

    /**
     *  Convierte un array en un objeto de equipamiento
     *
     * @param array $data
     * @return self
     */
    public function fromArray(array $data): self {
        if (isset($data['id'])) {
            $this->setId($data['id']);
        }
        
        return $this
            ->setName($data['name'])
            ->setType($data['type'])
            ->setMadeBy($data['made_by']);
    }

    /**
     *  Convierte un objeto de equipamiento en un array
     *
     * @return array
     */
    public function toArray(): array {
        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'made_by' => $this->made_by
        ];

        if (isset($this->id)) {
            $data['id'] = $this->id;
        }

        return $data;
    }
}
