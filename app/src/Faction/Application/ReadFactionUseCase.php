<?php

namespace App\Faction\Application;

use App\Faction\Domain\FactionRepository;

class ReadFactionUseCase {

    public function __construct(private FactionRepository $repository)
    {
    }

    public function execute(): array
    {
        return $this->repository->findAll();
    }
    
}