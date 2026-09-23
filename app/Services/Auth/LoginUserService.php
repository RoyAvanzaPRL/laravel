<?php

namespace App\Services\Auth;

use App\Exceptions\Auth\InactiveAccountException;
use App\Repositories\Users\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginUserService
{
    public function __construct(
        private UserRepository $users,
    ) {}

    public function handle(string $email, string $password): AuthResult
    {
        $user = $this->users->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->is_active) {
            throw new InactiveAccountException;
        }

        $token = $user->createToken('api', ['*'])->plainTextToken;

        return new AuthResult($user, $token);
    }
}
