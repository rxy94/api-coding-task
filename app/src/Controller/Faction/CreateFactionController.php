<?php

namespace App\Controller\Faction;

use PDO;
use App\Model\Faction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CreateFactionController {

    public function __construct(private PDO $pdo) 
    { 
    }

    public function __invoke(Request $request, Response $response, array $args): Response {
        # Obtenemos los datos del cuerpo de la petición
        $data = $request->getParsedBody();

        # Validamos los campos requeridos
        $requiredFields = ['faction_name', 'description'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $response->getBody()->write(json_encode([
                    'error' => "Missing required field: {$field}"
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }
        
        try {
            # Creamos una nueva instancia de Faction
            $faction = new Faction($this->pdo);
            $faction->setName($data['faction_name'])
                   ->setDescription($data['description']);
            
            # Guardamos la facción en la base de datos
            $result = $faction->save();
            
            if (!$result) {
                throw new \Exception('Error al guardar la facción');
            }
            
            # Devolvemos una respuesta de éxito usando toArray
            $response->getBody()->write(json_encode([
                'id' => $faction->getId(),
                'message' => 'La facción se ha creado correctamente'
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
            
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Error al crear la facción',
                'message' => $e->getMessage()
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
