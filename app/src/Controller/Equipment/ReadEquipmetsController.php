<?php

namespace App\Controller\Equipment;

use PDO;
use App\Model\Equipment;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReadEquipmetsController {

    public function __construct(private PDO $pdo) 
    { 
    }

    public function __invoke(Request $request, Response $response, array $args): Response {
        try {
            # Creamos una instancia del modelo Equipment
            $equipment = new Equipment($this->pdo);
            
            # Si hay un ID en los argumentos, buscamos ese equipamiento específico
            if (isset($args['id'])) {
                $id = (int) $args['id'];
                $foundEquipment = $equipment->findById($id);
                
                if (!$foundEquipment) {
                    $response->getBody()->write(json_encode([
                        'error' => 'Equipamiento no encontrado',
                        'message' => "No existe un equipamiento con el ID {$id}"
                    ]));
                    
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
                }
                
                $response->getBody()->write(json_encode([
                    'data' => $foundEquipment->toArray(),
                    'message' => 'Equipamiento encontrado correctamente'
                ]));
                
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            }
            
            # Si no hay ID, obtenemos todos los equipamientos
            $equipments = $equipment->findAll();
            
            # Convertimos los objetos Equipment a arrays
            $equipmentsArray = array_map(function($equipment) {
                return $equipment->toArray();
            }, $equipments);
            
            # Devolvemos una respuesta de éxito
            $response->getBody()->write(json_encode([
                'data' => $equipmentsArray,
                'message' => 'Equipamientos obtenidos correctamente'
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Error al obtener los equipamientos',
                'message' => $e->getMessage(),
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
