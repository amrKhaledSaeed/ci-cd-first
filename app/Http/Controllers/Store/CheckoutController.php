<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\CheckoutSimulationRequest;
use App\Services\Cart\CartService;
use App\Services\Checkout\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(CartService $cartService): View
    {
        return view('store.checkout.index', [
            'cart' => $cartService->summary(),
        ]);
    }

    public function store(CheckoutSimulationRequest $request, CheckoutService $checkoutService): RedirectResponse
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $checkoutService->simulateAndStore($validated);

        return redirect()->route('checkout.confirmation');
    }

    public function confirmation(CheckoutService $checkoutService): View|RedirectResponse
    {
        $simulation = $checkoutService->simulationFromSession();

        if ($simulation === []) {
            return redirect()
                ->route('checkout.index')
                ->withErrors([
                    'checkout' => __('No checkout simulation found. Please complete checkout first.'),
                ]);
        }

        return view('store.checkout.confirmation', [
            'simulation' => $simulation,
        ]);
    }
}
