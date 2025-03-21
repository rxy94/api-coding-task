<?php

namespace App\Controller\Faction;

use PDO;
use App\Model\Faction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReadFactionsController {

    public function __construct(private PDO $pdo) 
    { 
    }

    public function __invoke(Request $request, Response $response, array $args): Response {
        try {
            # Creamos una instancia del modelo Faction
            $faction = new Faction($this->pdo);
            
            # Si hay un ID en los argumentos, buscamos esa facción específica
            if (isset($args['id'])) {
                $id = (int) $args['id'];
                $foundFaction = $faction->findById($id);
                
                if (!$foundFaction) {
                    $response->getBody()->write(json_encode([
                        'error' => 'Facción no encontrada',
                        'message' => "No existe una facción con el ID {$id}"
                    ]));
                    
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
                }
                
                $response->getBody()->write(json_encode([
                    'data' => $foundFaction->toArray(),
                    'message' => 'Facción encontrada correctamente'
                ]));
                
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            }
            
            # Si no hay ID, obtenemos todas las facciones
            $factions = $faction->findAll();
            
            # Convertimos los objetos Faction a arrays
            $factionsArray = array_map(function($faction) {
                return $faction->toArray();
            }, $factions);
            
            # Devolvemos una respuesta de éxito
            $response->getBody()->write(json_encode([
                'data' => $factionsArray,
                'message' => 'Facciones obtenidas correctamente'
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Error al obtener las facciones',
                'message' => $e->getMessage(),
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
