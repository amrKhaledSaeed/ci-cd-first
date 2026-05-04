<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Data\Auth\RegisterData;
use App\Services\Auth\RegisterUserService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Register extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(RegisterUserService $registerUserService): void
    {
        /** @var array{name: string, email: string, password: string} $validated */
        $validated = $this->validate(
            RegisterData::rules(),
            messages: RegisterData::messages(),
        );

        $user = $registerUserService->register(RegisterData::fromArray($validated));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}
