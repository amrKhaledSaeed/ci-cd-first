<?php

declare(strict_types=1);

use App\Http\Controllers\Store\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductController::class, 'index'])->name('index');
Route::get('{product}', [ProductController::class, 'show'])
    ->name('show')
    ->missing(fn () => redirect()->route('products.index')->withErrors([
        'product' => __('The requested product was not found.'),
    ]));
