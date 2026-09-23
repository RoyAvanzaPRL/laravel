<?php

namespace App\Services\Auth;

use App\Repositories\Users\UserRepository;

class RegisterUserService
{
    public function __construct(
        private UserRepository $users,
    ) {}

    public function handle(string $name, string $email, string $password): AuthResult
    {
        $user = $this->users->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'is_active' => true,
        ]);

        $user->assignRole('customer');

        $token = $user->createToken('api', ['*'])->plainTextToken;

        return new AuthResult($user, $token);
    }
}
