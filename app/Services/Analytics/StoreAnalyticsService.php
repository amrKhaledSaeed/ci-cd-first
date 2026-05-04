<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Models\Product;
use App\Models\User;
use App\Repositories\Analytics\StoreAnalyticsEventRepository;
use Illuminate\Support\Facades\Log;
use Throwable;

class StoreAnalyticsService
{
    public function __construct(private StoreAnalyticsEventRepository $storeAnalyticsEventRepository) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public function track(string $eventName, ?User $user = null, ?Product $product = null, array $context = []): void
    {
        try {
            $this->storeAnalyticsEventRepository->track([
                'event_name' => $eventName,
                'user_id' => $user?->id,
                'product_id' => $product?->id,
                'context' => $context,
                'occurred_at' => now(),
            ]);
        } catch (Throwable $exception) {
            Log::warning('Store analytics event failed.', [
                'event_name' => $eventName,
                'user_id' => $user?->id,
                'product_id' => $product?->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
