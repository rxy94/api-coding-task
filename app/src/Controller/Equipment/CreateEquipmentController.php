<?php

namespace App\Controller\Equipment;

use PDO;
use App\Model\Equipment;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CreateEquipmentController {

    public function __construct(private PDO $pdo) 
    { 
    }

    public function __invoke(Request $request, Response $response, array $args): Response {
        # Obtenemos los datos del cuerpo de la petición
        $data = $request->getParsedBody();

        # Validamos los campos requeridos
        $requiredFields = ['name', 'type', 'made_by'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $response->getBody()->write(json_encode([
                    'error' => "Missing required field: {$field}"
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }
        
        try {
            # Creamos una nueva instancia de Equipment
            $equipment = new Equipment($this->pdo);
            $equipment->setName($data['name'])
                     ->setType($data['type'])
                     ->setMadeBy($data['made_by']);
            
            # Guardamos el equipamiento en la base de datos
            $result = $equipment->save();
            
            if (!$result) {
                throw new \Exception('Error al guardar el equipamiento');
            }
            
            # Devolvemos una respuesta de éxito
            $response->getBody()->write(json_encode([
                'id' => $equipment->getId(),
                'message' => 'El equipamiento se ha creado correctamente'
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
            
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Error al crear el equipamiento',
                'message' => $e->getMessage()
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
