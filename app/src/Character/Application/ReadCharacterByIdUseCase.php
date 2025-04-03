<?php

namespace App\Character\Application;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;
use App\Character\Domain\Service\CharacterValidator;

class ReadCharacterByIdUseCase {

    public function __construct(
        private CharacterRepository $repository
    ) {
    }

    public function execute(int $id): Character
    {
        $character = $this->repository->findById($id);

        return $character;
    }

}