<?php

declare(strict_types=1);

use App\Livewire\Auth\Login;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\withoutVite;

beforeEach(function () {
    withoutVite();
});

test('login merges session cart with persisted cart for authenticated user', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'slug' => 'persisted-merge-product',
        'stock' => 10,
        'is_active' => true,
        'price' => 50,
    ]);

    CartItem::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    session()->put('cart.lines', [
        $product->id => [
            'product_id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'unit_price' => 50.0,
            'quantity' => 3,
            'subtotal' => 150.0,
        ],
    ]);

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login')
        ->assertHasNoErrors();

    expect(session('cart.lines.'.$product->id.'.quantity'))->toBe(5);
    expect(session('cart.lines.'.$product->id.'.subtotal'))->toBe(250.0);

    $this->assertDatabaseHas('cart_items', [
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 5,
    ]);
});

test('authenticated user can add and remove wishlist items', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'slug' => 'wishlist-product',
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->post(route('wishlist.store', ['product' => $product]))
        ->assertRedirect();

    $this->assertDatabaseHas('wishlist_items', [
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);

    $this->actingAs($user)
        ->get(route('wishlist.index'))
        ->assertOk()
        ->assertSee($product->name);

    $this->actingAs($user)
        ->delete(route('wishlist.destroy', ['product' => $product]))
        ->assertRedirect();

    $this->assertDatabaseMissing('wishlist_items', [
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);
});

test('store analytics events are tracked for product view add to cart and checkout simulation', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'slug' => 'analytics-product',
        'stock' => 8,
        'price' => 22,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get(route('products.show', ['product' => $product]))
        ->assertOk();

    $this->actingAs($user)
        ->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ])
        ->assertRedirect();

    $this->actingAs($user)
        ->post(route('checkout.store'), [
            'customer_name' => 'Analytics User',
            'customer_email' => 'analytics@example.com',
            'shipping_address' => '123 Demo Street',
            'payment_method' => 'card',
        ])
        ->assertRedirect(route('checkout.confirmation'));

    $this->assertDatabaseHas('store_analytics_events', [
        'event_name' => 'product_view',
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);

    $this->assertDatabaseHas('store_analytics_events', [
        'event_name' => 'add_to_cart',
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);

    $this->assertDatabaseHas('store_analytics_events', [
        'event_name' => 'checkout_simulation',
        'user_id' => $user->id,
    ]);
});
