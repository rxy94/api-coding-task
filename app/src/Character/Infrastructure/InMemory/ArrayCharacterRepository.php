<?php

namespace App\Character\Infrastructure\InMemory;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterFactory;
use App\Character\Domain\CharacterRepository;
use App\Character\Domain\Exception\CharacterNotFoundException;

class ArrayCharacterRepository implements CharacterRepository
{
    public function __construct(
        private array $characters = []
    ) {
    }

    public function findById(int $id): ?Character
    {
        if (!isset($this->characters[$id])) {
            throw CharacterNotFoundException::build();
        }

        return $this->characters[$id];
    }

    public function save(Character $character): Character
    {
        if (null !== $character->getId()) {
            $this->characters[$character->getId()] = $character;

            return $character;
        }

        $character = CharacterFactory::build(
            $character->getName(),
            $character->getBirthDate(),
            $character->getKingdom(),
            $character->getEquipmentId(),
            $character->getFactionId(),
            count($this->characters) + 1
        );

        $this->characters[] = $character;

        return $character;

    }

    public function findAll(): array
    {
        return $this->characters;
    }

    public function delete(Character $character): bool
    {
        unset($this->characters[$character->getId()]);

        return true;
    }
}