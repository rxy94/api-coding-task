<?php

namespace App\Character\Domain;

interface CharacterRepository {

    public function findById(int $id): ?Character;

    public function findAll(): array;

    public function save(Character $character): Character;

    public function delete(Character $character): bool;


}