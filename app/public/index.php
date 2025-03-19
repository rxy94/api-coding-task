<?php

header('Content-type: application/json');

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/characters.php';

# Obtenemos la conexión a la base de datos
$db = getDatabaseConnection();

# Obtenemos el ID del personaje pasado por la URL si existe
$characterId = isset($_GET['id']) ? (int)$_GET['id'] : null;

# Si existe el ID del personaje, lo eliminamos
if ($characterId !== null) {
    # Eliminamos el personaje especificado
    deleteCharacterById($characterId, $db);

    # Devolvemos un mensaje de éxito
    echo json_encode([
        'status' => 'success',
        'message' => "Personaje con ID {$characterId} eliminado correctamente"
    ]);

}

# Si no se pasa ningún parámetro, devolvemos todos los personajes en formato JSON
$query = $db->query('SELECT * FROM characters');
$characters = $query->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "characters" => $characters,
]);

# Cerramos la conexión a la base de datos
$db = null;
