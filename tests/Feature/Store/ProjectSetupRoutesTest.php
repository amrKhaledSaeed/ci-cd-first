<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\User;

use function Pest\Laravel\withoutVite;

beforeEach(function () {
    withoutVite();
});

test('products index route is publicly accessible', function () {
    $this->get(route('products.index'))
        ->assertOk()
        ->assertSee('Products');
});

test('products show route is publicly accessible', function () {
    $product = Product::query()->create([
        'slug' => 'demo-product',
        'name' => 'Demo Product',
        'description' => 'Demo description',
        'price' => 99.90,
        'stock' => 20,
        'image' => '/images/demo-product.png',
        'category' => 'demo',
        'is_active' => true,
    ]);

    $this->get(route('products.show', ['product' => $product]))
        ->assertOk()
        ->assertSee('demo-product');
});

test('cart route is publicly accessible', function () {
    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('Cart');
});

test('checkout route requires authentication', function () {
    $this->get(route('checkout.index'))
        ->assertRedirect(route('login'));
});

test('authenticated user can access checkout routes', function () {
    $user = User::factory()->make();

    $this->actingAs($user)
        ->get(route('checkout.index'))
        ->assertOk()
        ->assertSee('Checkout');

    $this->actingAs($user)
        ->get(route('checkout.confirmation'))
        ->assertRedirect(route('checkout.index'))
        ->assertSessionHasErrors([
            'checkout' => 'No checkout simulation found. Please complete checkout first.',
        ]);
});
