<?php

namespace App\Character\Application;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Infrastructure\Persistence\Pdo\Exception\CharacterNotFoundException;

class UpdateCharacterUseCase
{
    public function __construct(
        private CharacterRepository $repository,
    ) {
    }

    public function execute(
        UpdateCharacterUseCaseRequest $request
    ): Character
    {
        $oldcharacter = $this->repository->findById($request->getId());

        if (!$oldcharacter) {
            throw CharacterNotFoundException::build();
        }

        $updatedcharacter = new Character(
            name: $request->getName(),
            birth_date: $request->getBirthDate(),
            kingdom: $request->getKingdom(),
            equipment_id: $request->getEquipmentId(),
            faction_id: $request->getFactionId(),
            id: $oldcharacter->getId()
        );

        return $this->repository->save($updatedcharacter);
    }
} 