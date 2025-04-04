<?php

namespace App\Character\Application;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Domain\Exception\CharacterNotFoundException;

class ReadCharacterByIdUseCase {

    public function __construct(
        private CharacterRepository $repository
    ) {
    }

    public function execute(int $id): Character
    {
        $character = $this->repository->findById($id);

        if (!$character) {
            throw CharacterNotFoundException::build();
        }

        return $character;
    }

}