<?php

declare(strict_types=1);

use App\Enums\UserType;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Hash;

test('database seeder creates the required customer test user', function () {
    $this->seed(DatabaseSeeder::class);

    $user = User::query()->where('email', 'user@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user?->user_type)->toBe(UserType::Customer);
    expect(Hash::check('password', $user?->password ?? ''))->toBeTrue();
});

test('database seeder creates seeded active products with deterministic image paths', function () {
    $this->seed(DatabaseSeeder::class);

    $products = Product::query()->get();

    expect($products->count())->toBeGreaterThanOrEqual(20)->toBeLessThanOrEqual(30);
    expect($products->where('is_active', true)->count())->toBe($products->count());
    expect($products->every(fn (Product $product): bool => str_starts_with($product->image ?? '', '/images/products/')))->toBeTrue();
    expect($products->every(fn (Product $product): bool => str_ends_with($product->image ?? '', '.jpg')))->toBeTrue();
});

test('database seeder creates the default categories catalog', function () {
    $this->seed(DatabaseSeeder::class);

    $categories = Category::query()->pluck('slug')->all();
    $expectedCategories = [
        'electronics',
        'home',
        'fashion',
        'sports',
        'beauty',
        'books',
    ];

    foreach ($expectedCategories as $expectedCategory) {
        expect($categories)->toContain($expectedCategory);
    }
});
