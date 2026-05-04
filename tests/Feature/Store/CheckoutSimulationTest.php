<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\User;

use function Pest\Laravel\withoutVite;

beforeEach(function () {
    withoutVite();
});

test('checkout simulation succeeds and clears cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'slug' => 'checkout-success-product',
        'stock' => 8,
        'price' => 40,
        'is_active' => true,
    ]);

    $this->post(route('cart.store'), [
        'product_id' => $product->id,
        'quantity' => 2,
    ])->assertRedirect();

    $response = $this->actingAs($user)->post(route('checkout.store'), [
        'customer_name' => 'Demo Customer',
        'customer_email' => 'demo@example.com',
        'shipping_address' => '123 Example Street',
        'payment_method' => 'card',
    ]);

    $response->assertRedirect(route('checkout.confirmation'));
    $response->assertSessionHas('checkout.simulation.confirmation_code');
    $response->assertSessionHas('checkout.simulation.totals.items_count', 2);
    $response->assertSessionHas('checkout.simulation.line_snapshot.0.name');

    expect(session('cart.lines'))->toBeNull();
});

test('checkout simulation fails for empty cart', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('checkout.store'), [
        'customer_name' => 'Demo Customer',
        'customer_email' => 'demo@example.com',
        'shipping_address' => '123 Example Street',
        'payment_method' => 'card',
    ]);

    $response->assertInvalid(['cart']);
});

test('checkout confirmation redirects back when simulation is missing', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('checkout.confirmation'));

    $response->assertRedirect(route('checkout.index'));
    $response->assertSessionHasErrors([
        'checkout' => 'No checkout simulation found. Please complete checkout first.',
    ]);
});
