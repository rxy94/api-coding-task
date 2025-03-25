<?php

namespace App\Character\Application;

use App\Character\Domain\Character;
use App\Character\Domain\CharacterRepository;

class ReadCharacterByIdUseCase {

    public function __construct(private CharacterRepository $repository)
    {
    }

    public function execute(int $id): Character
    {
        return $this->repository->findById($id);
    }

}