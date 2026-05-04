<?php

declare(strict_types=1);

use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

$middleware = [];
if (Features::canManageTwoFactorAuthentication() && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')) {
    $middleware[] = 'password.confirm';
}

Route::get('/two-factor', TwoFactor::class)
    ->middleware($middleware)
    ->name('two-factor.show');
