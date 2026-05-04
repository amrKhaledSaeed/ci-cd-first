<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Data\Auth\RegisterData;
use App\Enums\UserType;
use App\Models\User;
use App\Repositories\User\UserRepository;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;

class RegisterUserService
{
    public function __construct(private UserRepository $userRepository) {}

    public function register(RegisterData $registerData): User
    {
        $validated = $registerData->toArray();

        $user = $this->userRepository->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'user_type' => UserType::Customer->value,
        ]);

        event(new Registered($user));

        return $user;
    }
}
