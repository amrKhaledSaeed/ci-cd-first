<?php

declare(strict_types=1);

use App\Http\Controllers\Store\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WishlistController::class, 'index'])->name('index');
Route::post('/{product}', [WishlistController::class, 'store'])->name('store');
Route::delete('/{product}', [WishlistController::class, 'destroy'])->name('destroy');
