<?php

namespace App\Controller\Character;

use PDO;
use App\Model\Character;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReadCharactersController {

    public function __construct(private PDO $pdo) 
    { 
    }

    public function __invoke(Request $request, Response $response, array $args): Response {
        try {
            # Creamos una instancia del modelo Character
            $character = new Character($this->pdo);
            
            # Si hay un ID en los argumentos, buscamos ese personaje específico
            if (isset($args['id'])) {
                $id = (int) $args['id'];
                $foundCharacter = $character->findById($id);
                
                if (!$foundCharacter) {
                    $response->getBody()->write(json_encode([
                        'error' => 'Personaje no encontrado',
                        'message' => "No existe un personaje con el ID {$id}"
                    ]));
                    
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
                }
                
                $response->getBody()->write(json_encode([
                    'data' => $foundCharacter->toArray(),
                    'message' => 'Personaje encontrado correctamente'
                ]));
                
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            }
            
            # Si no hay ID, obtenemos todos los personajes
            $characters = $character->findAll();
            
            # Convertimos los objetos Character a arrays
            $charactersArray = array_map(function($character) {
                return $character->toArray();
            }, $characters);
            
            # Devolvemos una respuesta de éxito
            $response->getBody()->write(json_encode([
                'data' => $charactersArray,
                'message' => 'Personajes obtenidos correctamente'
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Error al obtener los personajes',
                'message' => $e->getMessage(),
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
