<?php

declare(strict_types=1);

use App\Support\RouteLoader;
use Illuminate\Support\Facades\Route;

Route::group([], function () {
    /*
     * ***********************************************
     * ****************** start guest ****************
     * ***********************************************
     */
    RouteLoader::load('/routes/web/guest/general/*.php');

    Route::prefix('products')->name('products.')->group(function () {
        RouteLoader::load('/routes/web/guest/products/*.php');
    });

    Route::prefix('cart')->name('cart.')->group(function () {
        RouteLoader::load('/routes/web/guest/cart/*.php');
    });

    /*
     * ***********************************************
     * ****************** start auth *****************
     * ***********************************************
     */
    Route::middleware('auth')->group(function () {
        RouteLoader::load('/routes/web/auth/general/*.php');

        Route::prefix('checkout')->name('checkout.')->group(function () {
            RouteLoader::load('/routes/web/auth/checkout/*.php');
        });

        Route::prefix('settings')->group(function () {
            RouteLoader::load('/routes/web/auth/settings/*.php');
        });

        Route::prefix('wishlist')->name('wishlist.')->group(function () {
            RouteLoader::load('/routes/web/auth/wishlist/*.php');
        });
    });
});

require __DIR__.'/auth.php';
