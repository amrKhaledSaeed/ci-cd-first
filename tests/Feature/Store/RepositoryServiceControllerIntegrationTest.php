<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\User;

use function Pest\Laravel\withoutVite;

beforeEach(function () {
    withoutVite();
});

test('product detail endpoint integrates controller service repository for deterministic fallback recommendations', function () {
    config([
        'services.ai.provider' => 'openai',
        'services.ai.openai.api_key' => '',
    ]);

    $product = Product::factory()->create([
        'slug' => 'integration-base-product',
        'category' => 'electronics',
        'price' => 100,
        'is_active' => true,
    ]);

    Product::factory()->create([
        'slug' => 'integration-rec-1',
        'name' => 'Integration Rec 1',
        'category' => 'electronics',
        'price' => 102,
        'is_active' => true,
    ]);

    Product::factory()->create([
        'slug' => 'integration-rec-2',
        'name' => 'Integration Rec 2',
        'category' => 'electronics',
        'price' => 98,
        'is_active' => true,
    ]);

    $response = $this->get(route('products.show', ['product' => $product->slug]));

    $response->assertOk();
    $response->assertSee('Source: fallback');
    $response->assertSeeInOrder(['Integration Rec 1', 'Integration Rec 2']);
});

test('store checkout flow integrates repository service controller and persists simulation snapshot', function () {
    $user = User::factory()->create();

    $product = Product::factory()->create([
        'slug' => 'integration-checkout-product',
        'name' => 'Integration Checkout Product',
        'category' => 'home',
        'price' => 55,
        'stock' => 10,
        'is_active' => true,
    ]);

    $this->post(route('cart.store'), [
        'product_id' => $product->id,
        'quantity' => 2,
    ])->assertRedirect();

    $response = $this->actingAs($user)->post(route('checkout.store'), [
        'customer_name' => 'Integration User',
        'customer_email' => 'integration@example.com',
        'shipping_address' => '42 Integration Street',
        'payment_method' => 'wallet',
    ]);

    $response->assertRedirect(route('checkout.confirmation'));
    $response->assertSessionHas('checkout.simulation.line_snapshot.0.name', 'Integration Checkout Product');
    $response->assertSessionHas('checkout.simulation.totals.items_count', 2);

    expect(session('cart.lines'))->toBeNull();
});
