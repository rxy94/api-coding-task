<?php

namespace App\Database;

use PDO;
use PDOException;

class Database {

    const DB_HOST = 'db';
    const DB_NAME = 'lotr';
    const DB_USER = 'root';
    const DB_PASSWORD = 'root';

    private static ?Database $instance = null;
    private PDO $db;

    private function __construct() {
        try {
            $this->db = new PDO("mysql:host=" . self::DB_HOST . ";dbname=" . self::DB_NAME, self::DB_USER, self::DB_PASSWORD);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new PDOException("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }

    private function __clone() {}

    /**
     * Obtiene la única instancia de la clase Database
     *
     * @return Database
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtiene la conexión PDO
     *
     * @return PDO
     */
    public function getConnection(): PDO {
        return $this->db;
    }
} 