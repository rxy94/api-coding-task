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
        private ?LoggerInterface $logger
    ) {
    }

    private function getKey(string $id): string
    {
        return __CLASS__ .":{$id}";
    }

    public function findById(int $id): ?Equipment
    {
        $cachedEquipment = $this->redis->get($this->getKey($id));

        if ($cachedEquipment) {
            $this->logger->info('Equipo encontrado en caché', ['id' => $id]);

            return unserialize($cachedEquipment);
        }

        $equipment = $this->mySQLEquipmentRepository->findById($id);
        $this->redis->set($this->getKey($id), serialize($equipment));

        return $equipment;

    }

    public function findAll(): array
    {
       
        $cachedEquipments = $this->redis->get($this->getKey('all'));

        if ($cachedEquipments) {
            $this->logger->info('Obteniendo todos los equipos de la caché');    

            return unserialize($cachedEquipments);
        }

        $equipments = $this->mySQLEquipmentRepository->findAll();
        $this->redis->set($this->getKey('all'), serialize($equipments));

        return $equipments; 

    }

    public function save(Equipment $equipment): Equipment
    {

        $savedEquipment = $this->mySQLEquipmentRepository->save($equipment);

        $this->redis->set($this->getKey($savedEquipment->getId()), serialize($savedEquipment));

        return $savedEquipment;

    }

    public function delete(Equipment $equipment): bool
    {
       
        $this->mySQLEquipmentRepository->delete($equipment);
        $this->redis->del($this->getKey($equipment->getId()));

        return true;

    }
}