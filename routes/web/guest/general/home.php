<?php

declare(strict_types=1);

use App\Livewire\Store\HomePage;
use Illuminate\Support\Facades\Route;

Route::get('/', HomePage::class)->name('home');
