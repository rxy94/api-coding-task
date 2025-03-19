<?php

define('DB_HOST', 'db');
define('DB_USER', 'root');
define('DB_PASSWORD', 'root');
define('DB_NAME', 'lotr');

/**
 * Conecta a la base de datos
 *
 * @return PDO
 */
function getDatabaseConnection(): PDO {

    try {
        $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    } catch (PDOException $e) {
        echo json_encode([
            'status' => 'Error de conexión a la base de datos',
            'message' => $e->getMessage(),
        ]);

    }

    return $db;

}




