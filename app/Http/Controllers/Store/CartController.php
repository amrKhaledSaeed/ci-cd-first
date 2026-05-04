<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\AddToCartRequest;
use App\Http\Requests\Store\UpdateCartItemRequest;
use App\Services\Cart\CartActionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Smpita\TypeAs\TypeAs;

class CartController extends Controller
{
    public function index(): View
    {
        return view('store.cart.index');
    }

    public function store(
        AddToCartRequest $request,
        CartActionService $cartActionService,
    ): RedirectResponse {
        /** @var array{product_id: int, quantity: int} $validated */
        $validated = $request->validated();
        $resolvedUser = auth()->check() ? user() : null;
        $cartActionService->add($validated, $resolvedUser);

        return back()->with('status', __('Product added to cart.'));
    }

    public function update(
        UpdateCartItemRequest $request,
        int $productId,
        CartActionService $cartActionService,
    ): RedirectResponse {
        /** @var array{quantity: int} $validated */
        $validated = $request->validated();
        $resolvedUser = auth()->check() ? user() : null;
        $cartActionService->update($productId, TypeAs::int($validated['quantity']), $resolvedUser);

        return back()->with('status', __('Cart updated.'));
    }

    public function destroy(
        int $productId,
        CartActionService $cartActionService,
    ): RedirectResponse {
        $resolvedUser = auth()->check() ? user() : null;
        $cartActionService->remove($productId, $resolvedUser);

        return back()->with('status', __('Item removed from cart.'));
    }

    public function clear(
        CartActionService $cartActionService,
    ): RedirectResponse {
        $resolvedUser = auth()->check() ? user() : null;
        $cartActionService->clear($resolvedUser);

        return back()->with('status', __('Cart cleared.'));
    }
}
