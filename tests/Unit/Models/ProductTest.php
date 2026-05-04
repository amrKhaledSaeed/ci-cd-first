<?php

declare(strict_types=1);

use App\Models\Product;
use Illuminate\Support\Facades\Schema;

it('uses slug for route model binding', function () {
    expect((new Product)->getRouteKeyName())->toBe('slug');
});

it('has required products table columns', function () {
    expect(Schema::hasColumns('products', [
        'slug',
        'name',
        'description',
        'price',
        'stock',
        'image',
        'category_id',
        'is_active',
    ]))->toBeTrue();
});

it('casts core product attributes', function () {
    $product = Product::query()->create([
        'slug' => 'cast-test-product',
        'name' => 'Cast Test Product',
        'description' => 'Cast test',
        'price' => 55.25,
        'stock' => 5,
        'image' => '/images/cast-test.png',
        'category' => 'test',
        'is_active' => 1,
    ])->fresh();

    expect($product)->not->toBeNull();
    expect($product?->is_active)->toBeBool();
    expect($product?->stock)->toBeInt();
});
