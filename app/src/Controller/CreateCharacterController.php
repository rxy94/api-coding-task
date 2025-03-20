<?php

namespace App\Controller;

use PDO;
use App\Model\Character;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CreateCharacterController {

    public function __construct(private PDO $pdo) 
    { 
    }


    public function __invoke(Request $request, Response $response, array $args): Response {
        # Obtenemos los datos del cuerpo de la petición
        $data = $request->getParsedBody();

        # Validamos los campos requeridos
        $requiredFields = ['name', 'birth_date', 'kingdom', 'equipment_id', 'faction_id'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $response->getBody()->write(json_encode([
                    'error' => "Missing required field: {$field}"
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

        }
        
        try {
            # Creamos una nueva instancia de Character
            $character = new Character($this->pdo);
            $character->setName($data['name'])
                      ->setBirthDate($data['birth_date'])
                      ->setKingdom($data['kingdom'])
                      ->setEquipmentId((int) $data['equipment_id'])
                      ->setFactionId((int) $data['faction_id']);
            
            # Guardamos el personaje en la base de datos
            $result = $character->save();
            
            if (!$result) {
                throw new \Exception('Error al guardar el personaje');
            }
            
            # Devolvemos una respuesta de éxito
            $response->getBody()->write(json_encode([
                'id' => $character->getId(),
                'message' => 'El personaje se ha creado correctamente'
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
            
        } catch (\Exception $e) {

            $response->getBody()->write(json_encode([
                'error' => 'Error al crear el personaje',
                'message' => $e->getMessage()
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);

        }

    }
}

