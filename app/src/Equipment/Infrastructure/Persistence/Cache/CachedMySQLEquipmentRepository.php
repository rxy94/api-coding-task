<?php

namespace App\Equipment\Infrastructure\Persistence\Cache;

use App\Equipment\Domain\Equipment;
use App\Equipment\Domain\EquipmentRepository;
use App\Equipment\Infrastructure\Persistence\Pdo\MySQLEquipmentRepository;
use Psr\Log\LoggerInterface;
use Redis;
use Exception;

class CachedMySQLEquipmentRepository implements EquipmentRepository
{
    public function __construct(
        private MySQLEquipmentRepository $mySQLEquipmentRepository,
        private Redis $redis,
        private LoggerInterface $logger
    ) {
    }

    public function findById(int $id): ?Equipment
    {
        try {
            $cachedEquipment = $this->redis->get("equipment:{$id}");

            if ($cachedEquipment) {
                $this->logger->info('Equipo encontrado en caché', ['id' => $id]);

                return unserialize($cachedEquipment);
            }

            $equipment = $this->mySQLEquipmentRepository->findById($id);
            $this->redis->set("equipment:{$id}", serialize($equipment));

            return $equipment;

        } catch (\Exception $e) {
            $this->logger->error('Error encontrando equipo en caché', ['id' => $id, 'error' => $e->getMessage()]);
            throw new Exception('Error encontrando equipo en caché', 500);      
        }
    }

    public function findAll(): array
    {
        try {
            $cachedEquipments = $this->redis->get('equipment:all');

            if ($cachedEquipments) {
                $this->logger->info('Obteniendo todos los equipos de la caché');    

                return unserialize($cachedEquipments);
            }

            $equipments = $this->mySQLEquipmentRepository->findAll();
            $this->redis->set('equipment:all', serialize($equipments));

            return $equipments; 

        } catch (\Exception $e) {
            $this->logger->error('Error encontrando todos los equipos en caché', ['error' => $e->getMessage()]);
            throw new Exception('Error encontrando todos los equipos en caché', 500);
        }
    }

    public function save(Equipment $equipment): Equipment
    {
        try {
            $savedEquipment = $this->mySQLEquipmentRepository->save($equipment);

            $this->redis->set("equipment:{$savedEquipment->getId()}", serialize($savedEquipment));

            return $savedEquipment;

        } catch (\Exception $e) {
            $this->logger->error('Error guardando equipo en caché', ['error' => $e->getMessage()]);
            throw new Exception('Error guardando equipo en caché', 500);        
        }
    }

    public function delete(Equipment $equipment): bool
    {
        try {
            $this->mySQLEquipmentRepository->delete($equipment);
            $this->redis->del("equipment:{$equipment->getId()}");

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Error eliminando equipo en caché', ['error' => $e->getMessage()]);
            throw new Exception('Error eliminando equipo en caché', 500);
        }
    }
}