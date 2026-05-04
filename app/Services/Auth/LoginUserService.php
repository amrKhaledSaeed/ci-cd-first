<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Data\Auth\LoginData;
use App\Models\User;
use App\Repositories\User\UserRepository;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginUserService
{
    public function __construct(private UserRepository $userRepository) {}

    public function authenticate(LoginData $loginData): User
    {
        $throttleKey = $this->throttleKey($loginData);

        $this->ensureIsNotRateLimited($throttleKey);

        $user = $this->userRepository->findByEmail($loginData->email);

        if (! $user || ! Hash::check($loginData->password, $user->password)) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($throttleKey);

        return $user;
    }

    private function ensureIsNotRateLimited(string $throttleKey): void
    {
        if (! RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($throttleKey);

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    private function throttleKey(LoginData $loginData): string
    {
        return Str::transliterate(Str::lower($loginData->email).'|'.request()->ip());
    }
}
