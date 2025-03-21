<?php

namespace App\Model;

use PDO;
use PDOException;

class Faction {
    private ?int $id = null;
    private string $faction_name;
    private string $description;
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    # Getters y setters
    public function setId(?int $id): self {
        $this->id = $id;
        return $this;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function setName(string $faction_name): self {
        $this->faction_name = $faction_name;
        return $this;
    }

    public function getName(): string {
        return $this->faction_name;
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
                $sql = "INSERT INTO factions (faction_name, description) VALUES (:faction_name, :description)";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    ':faction_name' => $this->faction_name,
                    ':description' => $this->description
                ]);
                $this->id = (int) $this->pdo->lastInsertId();
            } else {
                // Update
                $sql = "UPDATE factions SET faction_name = :faction_name, description = :description WHERE id = :id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    ':id' => $this->id,
                    ':faction_name' => $this->faction_name,
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
     * @return self|null
     */
    public function findById(int $id): ?self {
        try {
            $sql = "SELECT * FROM factions WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                return null;
            }

            return $this->fromArray($data);
            
        } catch (PDOException $e) {
            throw new PDOException("Error al buscar la facción: " . $e->getMessage());
        }
    }

    /**
     * Busca todas las facciones
     *
     * @return array
     */
    public function findAll(): array {
        try {
            $sql = "SELECT * FROM factions";
            $stmt = $this->pdo->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $factions = [];
            foreach ($data as $row) {
                $faction = new self($this->pdo);
                $factions[] = $faction->fromArray($row);
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
     * @return self
     */
    public function fromArray(array $data): self {
        if (isset($data['id'])) {
            $this->setId($data['id']);
        }
        
        return $this
            ->setName($data['faction_name'])
            ->setDescription($data['description']);
    }

    /**
     * Convierte la facción a un array
     *
     * @return array
     */
    public function toArray(): array {
        $data = [
            'faction_name' => $this->faction_name,
            'description' => $this->description
        ];

        if (isset($this->id)) {
            $data['id'] = $this->id;
        }

        return $data;
    }

}
