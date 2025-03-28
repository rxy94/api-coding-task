<?php

namespace App\Faction\Infrastructure\Persistence\Cache;

use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionRepository;
use App\Faction\Infrastructure\Persistence\Pdo\MySQLFactionRepository;
use Exception;
use Psr\Log\LoggerInterface;
use Redis;

class CachedMySQLFactionRepository implements FactionRepository
{
    public function __construct(
        private MySQLFactionRepository $mySQLFactionRepository,
        private Redis $redis,
        private ?LoggerInterface $logger
    ) {
    }

    private function getKey(string $id): string
    {
        return __CLASS__ .":{$id}";
    }   

    public function findAll(): array
    {
        $cachedFactions = $this->redis->get($this->getKey('all'));

        if ($cachedFactions) {
            $this->logger->info('Obteniendo todas las facciones de la caché');

            return unserialize($cachedFactions);
        }

        $factions = $this->mySQLFactionRepository->findAll();
        $this->redis->set($this->getKey('all'), serialize($factions));

        return $factions;   

    }

    public function findById(int $id): ?Faction
    {
        $cachedFaction = $this->redis->get($this->getKey($id));

        if ($cachedFaction) {
                $this->logger->info("Obteniendo la facción {$id} de la caché");
            return unserialize($cachedFaction);
        }

        $faction = $this->mySQLFactionRepository->findById($id);
        $this->redis->set($this->getKey($id), serialize($faction));
        
        return $faction;

    }
    
    public function save(Faction $faction): Faction
    {
    
        $savedFaction = $this->mySQLFactionRepository->save($faction);

        $this->redis->set($this->getKey($savedFaction->getId()), serialize($savedFaction));

        return $savedFaction;

    }

    public function delete(Faction $faction): bool
    {
       
        $deleted = $this->mySQLFactionRepository->delete($faction);
        $this->redis->del($this->getKey($faction->getId()));
        $this->redis->del($this->getKey('all'));

        $this->logger->info('Facción eliminada de la caché', ['id' => $faction->getId()]);

        return $deleted;

    }   

}
