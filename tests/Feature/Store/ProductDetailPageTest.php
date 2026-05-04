<?php

declare(strict_types=1);

use App\Models\Product;

use function Pest\Laravel\withoutVite;

beforeEach(function () {
    withoutVite();
});

test('product detail page shows core product information', function () {
    $product = Product::factory()->create([
        'name' => 'Studio Headphones',
        'slug' => 'studio-headphones',
        'category' => 'electronics',
        'price' => 149.99,
        'stock' => 12,
        'description' => 'Balanced audio for studio work.',
        'image' => '/images/products/studio-headphones.jpg',
        'is_active' => true,
    ]);

    $this->get('/products/'.$product->slug)
        ->assertOk()
        ->assertSee('Studio Headphones')
        ->assertSee('Electronics')
        ->assertSee('$149.99')
        ->assertSee('In stock (12)')
        ->assertSee('Balanced audio for studio work.');
});

test('add to cart request enforces stock-aware quantity limits', function () {
    $product = Product::factory()->create([
        'slug' => 'limited-stock-item',
        'stock' => 2,
        'is_active' => true,
    ]);

    $response = $this->post(route('cart.store'), [
        'product_id' => $product->id,
        'quantity' => 5,
    ]);

    $response->assertInvalid(['quantity']);
    $response->assertSessionHasErrors([
        'quantity' => 'Requested quantity exceeds available stock.',
    ]);
});

test('product detail page renders recommendation fallback content', function () {
    $product = Product::factory()->create([
        'name' => 'Trail Backpack',
        'slug' => 'trail-backpack',
        'category' => 'sports',
        'is_active' => true,
    ]);

    Product::factory()->create([
        'name' => 'Hiking Water Flask',
        'slug' => 'hiking-water-flask',
        'category' => 'sports',
        'is_active' => true,
    ]);

    $this->get('/products/'.$product->slug)
        ->assertOk()
        ->assertSee('Recommended for you')
        ->assertSee('Hiking Water Flask')
        ->assertSee('Source: fallback');
});

test('product detail redirects to listing when product is inactive', function () {
    $product = Product::factory()->create([
        'slug' => 'inactive-detail-product',
        'is_active' => false,
    ]);

    $this->get('/products/'.$product->slug)
        ->assertRedirect(route('products.index'))
        ->assertSessionHasErrors([
            'product' => 'The selected product is unavailable.',
        ]);
});

test('product detail missing slug redirects to listing with not-found message', function () {
    $this->get('/products/not-found-product-slug')
        ->assertRedirect(route('products.index'))
        ->assertSessionHasErrors([
            'product' => 'The requested product was not found.',
        ]);
});

test('product detail remembers last three viewed products in session', function () {
    $first = Product::factory()->create(['slug' => 'viewed-first', 'is_active' => true]);
    $second = Product::factory()->create(['slug' => 'viewed-second', 'is_active' => true]);
    $third = Product::factory()->create(['slug' => 'viewed-third', 'is_active' => true]);
    $fourth = Product::factory()->create(['slug' => 'viewed-fourth', 'is_active' => true]);

    $this->get(route('products.show', ['product' => $first]))->assertOk();
    $this->get(route('products.show', ['product' => $second]))->assertOk();
    $this->get(route('products.show', ['product' => $third]))->assertOk();
    $this->get(route('products.show', ['product' => $fourth]))->assertOk();

    expect(session('store.viewed_products'))->toBe([
        $fourth->id,
        $third->id,
        $second->id,
    ]);
});
