<?php

namespace App\Character\Application;

use App\Character\Domain\CharacterRepository;

class DeleteCharacterUseCase
{
    public function __construct(private CharacterRepository $repository)
    {
    }

    public function execute(int $id): void
    {
        $character = $this->repository->findById($id);

        if (!$character) {
            throw new \Exception("Personaje no encontrado con ID: {$id}");
        }

        $this->repository->delete($character);
    }
} 