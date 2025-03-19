<?php

require_once __DIR__ . '/database.php';

$db = getDatabaseConnection();

# Obtenemos todos los personajes
$query = $db->query('SELECT * FROM characters');

# Obtenemos los resultados
$characters = $query->fetchAll(PDO::FETCH_ASSOC);

# Devolvemos los resultados en formato JSON
echo json_encode($characters);

/**
 * Elimina un personaje de la base de datos
 *
 * @param integer $id
 * @param PDO $db
 * @return void
 */
function deleteCharacterById(int $id, PDO $db): void {

    $query = $db->prepare('DELETE FROM characters WHERE id = :id');
    $query->execute(['id' => $id]);

}