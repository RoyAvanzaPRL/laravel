<?php

namespace App\Repositories\Users;

use App\Models\User;

class EloquentUserRepository implements UserRepository
{
    public function findByIdOrFail(int $id): User
    {
        return User::query()->findOrFail($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    public function create(array $attributes): User
    {
        return User::query()->create($attributes);
    }
}
