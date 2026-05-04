<?php

declare(strict_types=1);

use App\Models\Product;

use function Pest\Laravel\withoutVite;

beforeEach(function () {
    withoutVite();
});

test('cart supports add update remove and clear operations', function () {
    $product = Product::factory()->create([
        'slug' => 'cart-flow-item',
        'name' => 'Cart Flow Item',
        'price' => 25.50,
        'stock' => 20,
        'is_active' => true,
    ]);

    $this->post(route('cart.store'), [
        'product_id' => $product->id,
        'quantity' => 2,
    ])->assertRedirect();

    expect(session('cart.lines.'.$product->id.'.product_id'))->toBe($product->id);
    expect(session('cart.lines.'.$product->id.'.name'))->toBe('Cart Flow Item');
    expect(session('cart.lines.'.$product->id.'.unit_price'))->toBe(25.50);
    expect(session('cart.lines.'.$product->id.'.quantity'))->toBe(2);
    expect(session('cart.lines.'.$product->id.'.subtotal'))->toBe(51.00);

    $this->patch(route('cart.update', ['productId' => $product->id]), [
        'quantity' => 3,
    ])->assertRedirect()->assertSessionHas('status', 'Cart updated.');

    expect(session('cart.lines.'.$product->id.'.quantity'))->toBe(3);
    expect(session('cart.lines.'.$product->id.'.subtotal'))->toBe(76.50);

    $this->delete(route('cart.destroy', ['productId' => $product->id]))
        ->assertRedirect()
        ->assertSessionHas('status', 'Item removed from cart.');

    expect(session('cart.lines'))->toBe([]);

    $this->post(route('cart.store'), [
        'product_id' => $product->id,
        'quantity' => 1,
    ])->assertRedirect();

    $this->delete(route('cart.clear'))
        ->assertRedirect()
        ->assertSessionHas('status', 'Cart cleared.');

    expect(session('cart.lines'))->toBeNull();
});

test('cart index removes stale inactive items', function () {
    $product = Product::factory()->create([
        'slug' => 'inactive-after-add',
        'stock' => 6,
        'is_active' => true,
    ]);

    $this->post(route('cart.store'), [
        'product_id' => $product->id,
        'quantity' => 2,
    ])->assertRedirect();

    $product->update(['is_active' => false]);

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('Some cart items were adjusted or removed because they are no longer available.')
        ->assertSee('Your cart is empty.');

    expect(session('cart.lines'))->toBe([]);
});

test('cart index clamps line quantity when stock decreases', function () {
    $product = Product::factory()->create([
        'slug' => 'stock-drop-item',
        'stock' => 10,
        'is_active' => true,
    ]);

    $this->post(route('cart.store'), [
        'product_id' => $product->id,
        'quantity' => 5,
    ])->assertRedirect();

    $product->update(['stock' => 2]);

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('Some cart items were adjusted or removed because they are no longer available.');

    expect(session('cart.lines.'.$product->id.'.quantity'))->toBe(2);
});

test('cart badge in store layout reflects current item count', function () {
    $product = Product::factory()->create([
        'slug' => 'badge-item',
        'stock' => 10,
        'is_active' => true,
    ]);

    $this->post(route('cart.store'), [
        'product_id' => $product->id,
        'quantity' => 4,
    ])->assertRedirect();

    $this->get(route('products.index'))
        ->assertOk()
        ->assertSee('wire:name="store.cart-badge"', false)
        ->assertSee('count&quot;:4', false);
});

test('cart update returns validation error when quantity exceeds stock', function () {
    $product = Product::factory()->create([
        'slug' => 'limited-update-stock',
        'stock' => 2,
        'is_active' => true,
    ]);

    $this->post(route('cart.store'), [
        'product_id' => $product->id,
        'quantity' => 1,
    ])->assertRedirect();

    $response = $this->from(route('cart.index'))->patch(route('cart.update', ['productId' => $product->id]), [
        'quantity' => 5,
    ]);

    $response->assertRedirect(route('cart.index'));
    $response->assertSessionHasErrors([
        'quantity' => 'Requested quantity exceeds available stock.',
    ]);

    expect(session('cart.lines.'.$product->id.'.quantity'))->toBe(1);
});

test('cart add returns user-friendly error when cumulative quantity exceeds stock', function () {
    $product = Product::factory()->create([
        'slug' => 'add-over-stock-product',
        'stock' => 2,
        'is_active' => true,
    ]);

    $this->post(route('cart.store'), [
        'product_id' => $product->id,
        'quantity' => 2,
    ])->assertRedirect();

    $response = $this->from(route('products.show', ['product' => $product->slug]))
        ->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

    $response->assertRedirect(route('products.show', ['product' => $product->slug]));
    $response->assertSessionHasErrors([
        'quantity' => 'Requested quantity exceeds available stock.',
    ]);
});
