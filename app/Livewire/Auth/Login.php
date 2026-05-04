<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Data\Auth\LoginData;
use App\Services\Auth\LoginUserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Fortify\Features;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(LoginUserService $loginUserService): void
    {
        /** @var array{email: string, password: string} $validated */
        $validated = $this->validate(
            LoginData::rules(),
            messages: LoginData::messages(),
        );

        $loginData = LoginData::fromArray([
            ...$validated,
            'remember' => $this->remember,
        ]);

        $user = $loginUserService->authenticate($loginData);

        if (Features::canManageTwoFactorAuthentication() && $user->hasEnabledTwoFactorAuthentication()) {
            Session::put([
                'login.id' => $user->getKey(),
                'login.remember' => $loginData->remember,
            ]);

            $this->redirect(route('two-factor.login'), navigate: true);

            return;
        }

        Auth::login($user, $loginData->remember);
        Session::regenerate();

        $this->redirect('/', navigate: false);
    }
}
