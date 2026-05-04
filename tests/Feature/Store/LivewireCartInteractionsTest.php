<?php

declare(strict_types=1);

use App\Livewire\Store\AddToCartForm;
use App\Livewire\Store\CartBadge;
use App\Livewire\Store\CartPanel;
use App\Models\Product;
use Livewire\Livewire;

use function Pest\Laravel\withoutVite;

beforeEach(function () {
    withoutVite();
});

test('livewire add to cart form adds a line without full page redirect', function () {
    $product = Product::factory()->create([
        'slug' => 'livewire-add-product',
        'stock' => 5,
        'is_active' => true,
    ]);

    Livewire::test(AddToCartForm::class, [
        'productId' => $product->id,
        'stock' => $product->stock,
    ])
        ->set('quantity', 2)
        ->call('addToCart')
        ->assertHasNoErrors();

    expect(session('cart.lines.'.$product->id.'.quantity'))->toBe(2);
});

test('livewire cart panel validates quantity against stock', function () {
    $product = Product::factory()->create([
        'slug' => 'livewire-stock-product',
        'stock' => 2,
        'is_active' => true,
    ]);

    $this->post(route('cart.store'), [
        'product_id' => $product->id,
        'quantity' => 1,
    ])->assertRedirect();

    Livewire::test(CartPanel::class)
        ->set('quantities.'.$product->id, 5)
        ->call('updateLine', $product->id)
        ->assertHasErrors([
            'quantities.'.$product->id,
        ]);

    expect(session('cart.lines.'.$product->id.'.quantity'))->toBe(1);
});

test('livewire cart panel updates rendered subtotal when quantity changes before submit', function () {
    $product = Product::factory()->create([
        'slug' => 'livewire-preview-subtotal',
        'price' => 10.00,
        'stock' => 10,
        'is_active' => true,
    ]);

    $this->post(route('cart.store'), [
        'product_id' => $product->id,
        'quantity' => 1,
    ])->assertRedirect();

    Livewire::test(CartPanel::class)
        ->set('quantities.'.$product->id, 3)
        ->assertSee('$30.00');
});

test('livewire cart panel removes and clears cart lines', function () {
    $firstProduct = Product::factory()->create([
        'slug' => 'livewire-remove-first',
        'stock' => 5,
        'is_active' => true,
    ]);
    $secondProduct = Product::factory()->create([
        'slug' => 'livewire-remove-second',
        'stock' => 5,
        'is_active' => true,
    ]);

    $this->post(route('cart.store'), [
        'product_id' => $firstProduct->id,
        'quantity' => 1,
    ])->assertRedirect();
    $this->post(route('cart.store'), [
        'product_id' => $secondProduct->id,
        'quantity' => 1,
    ])->assertRedirect();

    Livewire::test(CartPanel::class)
        ->call('removeLine', $firstProduct->id)
        ->assertHasNoErrors();

    expect(session('cart.lines.'.$firstProduct->id))->toBeNull();
    expect(session('cart.lines.'.$secondProduct->id.'.quantity'))->toBe(1);

    Livewire::test(CartPanel::class)
        ->call('clearCart')
        ->assertHasNoErrors();

    expect(session('cart.lines'))->toBeNull();
});

test('livewire cart badge refreshes after cart updates event', function () {
    $product = Product::factory()->create([
        'slug' => 'livewire-badge-product',
        'stock' => 5,
        'is_active' => true,
    ]);

    $badge = Livewire::test(CartBadge::class)
        ->assertSet('count', 0);

    $this->post(route('cart.store'), [
        'product_id' => $product->id,
        'quantity' => 3,
    ])->assertRedirect();

    $badge
        ->dispatch('cart-updated')
        ->assertSet('count', 3)
        ->assertSee('3');
});
