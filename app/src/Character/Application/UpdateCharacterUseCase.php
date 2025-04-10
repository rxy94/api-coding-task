<?php

namespace App\Character\Application;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Domain\Exception\CharacterNotFoundException;

class UpdateCharacterUseCase
{
    public function __construct(
        private CharacterRepository $repository,
    ) {
    }

    public function execute(
        UpdateCharacterUseCaseRequest $request
    ): UpdateCharacterUseCaseResponse
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

        $savedcharacter = $this->repository->save($updatedcharacter);

        return new UpdateCharacterUseCaseResponse($savedcharacter);
    }
} 