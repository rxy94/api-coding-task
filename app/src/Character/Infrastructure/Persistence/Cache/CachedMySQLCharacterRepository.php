<?php

namespace App\Character\Infrastructure\Persistence\Cache;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Infrastructure\Persistence\Pdo\MySQLCharacterRepository;
use Psr\Log\LoggerInterface;
use Redis;

class CachedMySQLCharacterRepository implements CharacterRepository
{
    public function __construct(
        private MySQLCharacterRepository $mySQLCharacterRepository,
        private Redis $redis,
        private ?LoggerInterface $logger
    ) {
    }

    private function getKey(string $id): string
    {
        return __CLASS__ .":{$id}";
    }

    public function findById(int $id): ?Character
    {
        $cachedCharacter = $this->redis->get($this->getKey($id));

        if ($cachedCharacter) {
            $this->logger->info("Personaje encontrado en caché: {$id}", ['id' => $id]);
            return unserialize($cachedCharacter);
        }

        $character = $this->mySQLCharacterRepository->findById($id);
        $this->redis->set($this->getKey($id), serialize($character));

        return $character;
    }

    public function findAll(): array
    {
        $cachedCharacters = $this->redis->get($this->getKey('all'));

        if ($cachedCharacters) {
            $this->logger->info('Obteniendo todos los personajes de la caché', ['cachedCharacters' => $cachedCharacters]);
            return unserialize($cachedCharacters);
        }

        $characters = $this->mySQLCharacterRepository->findAll();
        $this->redis->set($this->getKey('all'), serialize($characters));

        return $characters;
    }

    public function save(Character $character): Character
    {
        $savedCharacter = $this->mySQLCharacterRepository->save($character);

        $this->redis->set($this->getKey($savedCharacter->getId()), serialize($savedCharacter));

        return $savedCharacter;
    }

    public function delete(Character $character): bool
    {
        $this->mySQLCharacterRepository->delete($character);
        $this->redis->del($this->getKey($character->getId()));

        return true;
    }
}
