<?php

declare(strict_types=1);

use App\Http\Controllers\Store\CheckoutController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CheckoutController::class, 'index'])->name('index');
Route::post('/', [CheckoutController::class, 'store'])->name('store');
Route::get('/confirmation', [CheckoutController::class, 'confirmation'])->name('confirmation');
