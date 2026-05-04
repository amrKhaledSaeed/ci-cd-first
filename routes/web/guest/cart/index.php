<?php

declare(strict_types=1);

use App\Http\Controllers\Store\CartController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CartController::class, 'index'])->name('index');
Route::post('/', [CartController::class, 'store'])->name('store');
Route::patch('/{productId}', [CartController::class, 'update'])->name('update');
Route::delete('/{productId}', [CartController::class, 'destroy'])->name('destroy');
Route::delete('/', [CartController::class, 'clear'])->name('clear');
