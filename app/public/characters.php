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
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    $query->execute();
}

/**
 * Crea un nuevo personaje en la base de datos
 *
 * @param string $name
 * @param string $birthDate
 * @param string $kingdom
 * @param int $equipmentId
 * @param int $factionId
 * @param PDO $db
 * @return void
 */
function createCharacter(string $name, string $birthDate, string $kingdom, int $equipmentId, int $factionId, PDO $db): void {
    $query = $db->prepare('INSERT INTO characters (name, birth_date, kingdom, equipment_id, faction_id) VALUES (:name, :birth_date, :kingdom, :equipment_id, :faction_id)');
    
    // Vinculamos los parámetros con sus tipos correspondientes
    $query->bindParam(':name', $name, PDO::PARAM_STR);
    $query->bindParam(':birth_date', $birthDate, PDO::PARAM_STR);
    $query->bindParam(':kingdom', $kingdom, PDO::PARAM_STR);
    $query->bindParam(':equipment_id', $equipmentId, PDO::PARAM_INT);
    $query->bindParam(':faction_id', $factionId, PDO::PARAM_INT);
    
    $query->execute();
    
}
