<?php

namespace App\Model;

use PDO;
use PDOException;

class Faction {
    private ?int $id = null;
    private string $name;
    private string $description;

    public function __construct(private PDO $pdo)
    {
    }

    # Getters y setters
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

    public function setDescription(string $description): self {
        $this->description = $description;
        return $this;
    }

    public function getDescription(): string {
        return $this->description;
    }

    /**
     * Guarda la facción en la base de datos
     *
     * @return boolean
     */ 
    public function save(): bool {
        try {
            if ($this->id === null) {
                // Insert
                $sql = "INSERT INTO factions (name, description) VALUES (:name, :description)";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    ':name' => $this->name,
                    ':description' => $this->description
                ]);
                $this->id = (int) $this->pdo->lastInsertId();
            } else {
                // Update
                $sql = "UPDATE factions SET name = :name, description = :description WHERE id = :id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    ':id' => $this->id,
                    ':name' => $this->name,
                    ':description' => $this->description
                ]);
            }
            return true;

        } catch (PDOException $e) {
            throw new PDOException("Error al guardar la facción: " . $e->getMessage());

        }
    }

    /**
     * Elimina la facción de la base de datos
     *
     * @return boolean
     */
    public function delete(): bool {
        try {

            if ($this->id === null) {
                throw new PDOException("No se puede eliminar una facción sin ID");
            }

            $sql = "DELETE FROM factions WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $this->id]);
            return true;

        } catch (PDOException $e) {
            throw new PDOException("Error al eliminar la facción: " . $e->getMessage());

        }
    }

    /**
     * Busca una facción por su ID
     *
     * @param integer $id
     * @param PDO $pdo
     * @return self|null
     */
    public static function findById(int $id, PDO $pdo): ?self {
        try {

            $sql = "SELECT * FROM factions WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                return null;
            }

            $faction = new self($pdo);
            return $faction->fromArray($data, $pdo);
            
        } catch (PDOException $e) {
            throw new PDOException("Error al buscar la facción: " . $e->getMessage());

        }
    }

    /**
     * Busca todas las facciones
     *
     * @param PDO $pdo
     * @return array
     */
    public static function findAll(PDO $pdo): array {
        try {

            $sql = "SELECT * FROM factions";
            $stmt = $pdo->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $factions = [];
            foreach ($data as $row) {
                $faction = new self($pdo);
                $factions[] = $faction->fromArray($row, $pdo);
            }

            return $factions;

        } catch (PDOException $e) {
            throw new PDOException("Error al buscar las facciones: " . $e->getMessage());
            
        }
    }

    /**
     * Crea una facción a partir de un array
     *
     * @param array $data
     * @param PDO $pdo
     * @return self
     */
    public function fromArray(array $data, PDO $pdo): self {
        $faction = new self($pdo);
        
        if (isset($data['id'])) {
            $faction->setId($data['id']);
        }
        
        return $faction
            ->setName($data['name'])
            ->setDescription($data['description']);

    }

    /**
     * Convierte la facción a un array
     *
     * @return array
     */
    public function toArray(): array {
        $data = [
            'name' => $this->name,
            'description' => $this->description
        ];

        if (isset($this->id)) {
            $data['id'] = $this->id;
        }

        return $data;
    }

}
