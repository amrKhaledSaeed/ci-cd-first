<?php

declare(strict_types=1);

use App\Models\Product;

use function Pest\Laravel\withoutVite;

beforeEach(function () {
    withoutVite();
});

test('cart store endpoint adds product to session cart', function () {
    $product = Product::query()->create([
        'slug' => 'cart-product',
        'name' => 'Cart Product',
        'description' => 'Cart description',
        'price' => 10.00,
        'stock' => 10,
        'image' => '/images/cart-product.png',
        'category' => 'demo',
        'is_active' => true,
    ]);

    $response = $this->post(route('cart.store'), [
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('status', 'Product added to cart.');
    $response->assertSessionHas('cart.lines.'.$product->id.'.quantity', 2);
});

test('checkout store validates required fields', function () {
    $user = App\Models\User::factory()->create();

    $response = $this->actingAs($user)->post(route('checkout.store'), []);

    $response->assertInvalid([
        'customer_name',
        'customer_email',
        'shipping_address',
        'payment_method',
    ]);
});
