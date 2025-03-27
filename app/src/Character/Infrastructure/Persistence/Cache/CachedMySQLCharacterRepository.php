<?php

namespace App\Character\Infrastructure\Persistence\Cache;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Infrastructure\Persistence\Pdo\MySQLCharacterRepository;
use Psr\Log\LoggerInterface;
use Redis;
use Exception;

class CachedMySQLCharacterRepository implements CharacterRepository
{
    public function __construct(
        private MySQLCharacterRepository $mySQLCharacterRepository,
        private Redis $redis,
        private ?LoggerInterface $logger
    ) {
    }

    public function findById(int $id): ?Character
    {
        try {
            $cachedCharacter = $this->redis->get($id);

            if ($cachedCharacter) {
                $this->logger->info('Personaje encontrado en caché', ['id' => $id]);

                return unserialize($cachedCharacter);

            }

            $character = $this->mySQLCharacterRepository->findById($id);
            $this->redis->set($id, serialize($character));

            return $character;

        } catch (\Exception $e) {
            $this->logger->error('Error encontrando personaje en caché', ['id' => $id, 'error' => $e->getMessage()]);
            throw new Exception('Error encontrando personaje en caché', 500);
        }
    }

    public function findAll(): array
    {

        try {
            $cachedCharacters = $this->redis->get('all');

            if ($cachedCharacters) {
                $this->logger->info('Obteniendo todos los personajes de la caché');

                return unserialize($cachedCharacters);

            }

            $characters = $this->mySQLCharacterRepository->findAll();
            $this->redis->set('all', serialize($characters));

            return $characters;

        } catch (\Exception $e) {
            $this->logger->error('Error encontrando todos los personajes en caché', ['error' => $e->getMessage()]);
            throw new Exception('Error encontrando todos los personajes en caché', 500);
        }
    }

    public function save(Character $character): Character
    {
        try {
            $savedCharacter = $this->mySQLCharacterRepository->save($character);

            $this->redis->set($savedCharacter->getId(), serialize($savedCharacter));

            return $savedCharacter;

        } catch (\Exception $e) {
            $this->logger->error('Error guardando personaje en caché', ['error' => $e->getMessage()]);
            throw new Exception('Error guardando personaje en caché', 500);
        }
    }

    public function delete(Character $character): bool
    {
        try {
            $this->mySQLCharacterRepository->delete($character);
            $this->redis->del($character->getId());

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Error eliminando personaje en caché', ['error' => $e->getMessage()]);
            throw new Exception('Error eliminando personaje en caché', 500);
        }
    }
}
