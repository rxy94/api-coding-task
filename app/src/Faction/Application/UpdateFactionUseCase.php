<?php

namespace App\Faction\Application;

use App\Faction\Domain\Faction;
use App\Faction\Domain\FactionRepository;
use App\Faction\Domain\Exception\FactionNotFoundException;

class UpdateFactionUseCase
{
    public function __construct(
        private FactionRepository $repository,
    ) {
    }

    public function execute(
        UpdateFactionUseCaseRequest $request
    ): UpdateFactionUseCaseResponse
    {
        $oldFaction = $this->repository->findById($request->getId());

        if (!$oldFaction) {
            throw FactionNotFoundException::build();
        }

        $updatedFaction = new Faction(
            faction_name: $request->getName(),
            description: $request->getDescription(),
            id: $oldFaction->getId()
        );

        $savedFaction = $this->repository->save($updatedFaction);

        return new UpdateFactionUseCaseResponse($savedFaction);
    }
} 