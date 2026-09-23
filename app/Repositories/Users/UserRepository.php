<?php

namespace App\Repositories\Users;

use App\Models\User;

interface UserRepository
{
    public function findByIdOrFail(int $id): User;

    public function findByEmail(string $email): ?User;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): User;
}
