<?php

declare(strict_types=1);

use App\Models\Product;

it('refreshes product catalog using smartshop demo refresh command', function () {
    Product::factory()->create([
        'slug' => 'old-demo-product',
        'is_active' => true,
    ]);

    $this->artisan('smartshop:demo-refresh')
        ->assertExitCode(0);

    expect(Product::query()->count())->toBeGreaterThanOrEqual(20)
        ->and(Product::query()->where('slug', 'old-demo-product')->exists())->toBeFalse();
});
