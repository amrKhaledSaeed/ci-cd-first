<?php

declare(strict_types=1);

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/settings/profile');

Route::get('/profile', Profile::class)->name('settings.profile');
Route::get('/password', Password::class)->name('settings.password');
Route::get('/appearance', Appearance::class)->name('settings.appearance');
