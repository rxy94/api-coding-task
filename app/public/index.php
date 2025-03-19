<?php

header('Content-type: application/json');

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/characters.php';

# Devolvemos los resultados en formato JSON
echo json_encode([
    "characters" => $characters,
]);
