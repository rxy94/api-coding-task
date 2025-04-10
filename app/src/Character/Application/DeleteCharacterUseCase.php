<?php

namespace App\Character\Application;

use App\Character\Domain\CharacterRepository;
use App\Character\Domain\Exception\CharacterNotFoundException;

class DeleteCharacterUseCase
{
    public function __construct(
        private CharacterRepository $repository
    ) {
    }

    public function execute(int $id): void
    {
        $character = $this->repository->findById($id);

        if (!$character) {
            throw CharacterNotFoundException::build();
        }

        $this->repository->delete($character);
    }
} 