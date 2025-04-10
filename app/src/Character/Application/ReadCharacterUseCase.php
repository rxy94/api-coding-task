<?php

namespace App\Character\Application;

use App\Character\Domain\CharacterRepository;

class ReadCharacterUseCase {

    public function __construct(
        private CharacterRepository $repository
    ) {
    }

    public function execute(): array
    {
        $characters = $this->repository->findAll();

        return $characters;
    }

}