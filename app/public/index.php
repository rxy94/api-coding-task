<?php

header('Content-type: application/json');

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/characters.php';

# Obtenemos la conexión a la base de datos
$db = getDatabaseConnection();

# Obtenemos el ID del personaje y el parámetro delete de la URL si existen
$characterId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$delete = isset($_GET['delete']) ? (int)$_GET['delete'] : null;

# Si se pasa el parámetro delete y un ID, eliminamos el personaje
if ($delete && $characterId !== null) {
    
    try {

        deleteCharacterById($characterId, $db);

        # Devolvemos un mensaje de éxito
        echo json_encode([
            'status' => 'success',
            'message' => "Personaje con ID {$characterId} eliminado correctamente"
        ]);

    } catch (Exception $e) {

        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }

}

# Obtenemos los parámetros de creación del personaje de la URL si existen
$name = isset($_GET['name']) ? $_GET['name'] : null;
$birthDate = isset($_GET['birth_date']) ? $_GET['birth_date'] : null;
$kingdom = isset($_GET['kingdom']) ? $_GET['kingdom'] : null;
$equipmentId = isset($_GET['equipment_id']) ? (int)$_GET['equipment_id'] : null;
$factionId = isset($_GET['faction_id']) ? (int)$_GET['faction_id'] : null; 

# Si se pasa el parámetro create, creamos un nuevo personaje
if (isset($_GET['create'])) {

    try {
        # Validamos que todos los parámetros estén presentes
        if (!$name || !$birthDate || !$kingdom || !$equipmentId || !$factionId ) {
            throw new Exception('Todos los parámetros son obligatorios');
        }

        # Validamos el formato de la fecha
        $date = DateTime::createFromFormat('Y-m-d', $birthDate);
        if (!$date) {
            throw new Exception('El formato de fecha debe ser YYYY-MM-DD');
        }

        # Validamos que el equipamiento exista
        $equipmentQuery = $db->prepare('SELECT id FROM equipments WHERE id = :id');
        $equipmentQuery->execute(['id' => $equipmentId]);
        if (!$equipmentQuery->fetch()) {
            throw new Exception('El equipamiento especificado no existe');
        }

        # Validamos que la facción exista
        $factionQuery = $db->prepare('SELECT id FROM factions WHERE id = :id');
        $factionQuery->execute(['id' => $factionId]);
        if (!$factionQuery->fetch()) {
            throw new Exception('La facción especificada no existe');
        }

        # Creamos un nuevo personaje
        createCharacter($name, $birthDate, $kingdom, $equipmentId, $factionId, $db);

        echo json_encode([
            'status' => 'success',
            'message' => 'Personaje creado correctamente'
        ]);

    } catch (Exception $e) {
        
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }

}

# Si no se pasa ningún parámetro, devolvemos todos los personajes en formato JSON
$query = $db->query('SELECT * FROM characters');
$characters = $query->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($characters);

# Cerramos la conexión a la base de datos
$db = null;

