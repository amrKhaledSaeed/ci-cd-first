<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Wishlist\WishlistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(WishlistService $wishlistService): View
    {
        $user = user();

        return view('store.wishlist.index', [
            'products' => $wishlistService->paginateProducts($user),
        ]);
    }

    public function store(Product $product, WishlistService $wishlistService): RedirectResponse
    {
        $added = $wishlistService->addActive(user(), $product);

        if (! $added) {
            return back()->withErrors([
                'wishlist' => __('The selected product is unavailable.'),
            ]);
        }

        return back()->with('status', __('Added to wishlist.'));
    }

    public function destroy(Product $product, WishlistService $wishlistService): RedirectResponse
    {
        $user = user();

        $wishlistService->remove($user, $product);

        return back()->with('status', __('Removed from wishlist.'));
    }
}
