<?php

declare(strict_types=1);

namespace App\Services\Checkout;

use App\Exceptions\Domain\CheckoutOperationException;
use App\Services\Analytics\StoreAnalyticsService;
use App\Services\Cart\CartService;
use App\Services\Cart\PersistedCartService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Smpita\TypeAs\TypeAs;

class CheckoutService
{
    private const SIMULATION_SESSION_KEY = 'checkout.simulation';

    public function __construct(
        private CartService $cartService,
        private PersistedCartService $persistedCartService,
        private StoreAnalyticsService $storeAnalyticsService,
    ) {}

    /**
     * @param  array<string, mixed>  $checkoutData
     * @return array<string, mixed>
     */
    public function simulate(array $checkoutData): array
    {
        $cartSummary = $this->cartService->summary();

        $itemsCount = TypeAs::int($cartSummary['meta']['items_count'] ?? 0);
        $subtotal = TypeAs::float($cartSummary['meta']['subtotal'] ?? 0);

        if ($itemsCount < 1 || $subtotal <= 0 || $cartSummary['lines'] === []) {
            throw new CheckoutOperationException(__('Your cart is empty.'));
        }

        $shippingFee = 0.0;
        $grandTotal = round($subtotal + $shippingFee, 2);

        $result = [
            'confirmation_code' => 'CHK-'.Str::upper(Str::random(10)),
            'processed_at' => now()->toIso8601String(),
            'customer' => [
                'name' => $checkoutData['customer_name'] ?? null,
                'email' => $checkoutData['customer_email'] ?? null,
            ],
            'shipping' => [
                'address' => $checkoutData['shipping_address'] ?? null,
                'payment_method' => $checkoutData['payment_method'] ?? null,
            ],
            'totals' => [
                'items_count' => $itemsCount,
                'subtotal' => round($subtotal, 2),
                'shipping_fee' => $shippingFee,
                'grand_total' => $grandTotal,
            ],
            'line_snapshot' => $cartSummary['lines'],
        ];

        $this->storeAnalyticsService->track(
            eventName: 'checkout_simulation',
            user: user(),
            context: [
                'items_count' => $itemsCount,
                'grand_total' => $grandTotal,
            ],
        );
        $this->cartService->clear();
        $this->persistedCartService->clearPersistedForUser();

        return $result;
    }

    /**
     * @param  array<string, mixed>  $checkoutData
     */
    public function simulateAndStore(array $checkoutData): void
    {
        Session::put(self::SIMULATION_SESSION_KEY, $this->simulate($checkoutData));
    }

    /**
     * @return array<string, mixed>
     */
    public function simulationFromSession(): array
    {
        return TypeAs::array(Session::get(self::SIMULATION_SESSION_KEY, []), default: []);
    }
}
