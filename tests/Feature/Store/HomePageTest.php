<?php

declare(strict_types=1);

use App\Models\Product;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\withoutVite;

beforeEach(function () {
    withoutVite();
});

test('products index shows only active products', function () {
    Product::factory()->create([
        'name' => 'Active Product',
        'slug' => 'active-product',
        'is_active' => true,
    ]);

    Product::factory()->create([
        'name' => 'Inactive Product',
        'slug' => 'inactive-product',
        'is_active' => false,
    ]);

    $this->get(route('products.index'))
        ->assertOk()
        ->assertSee('Active Product')
        ->assertDontSee('Inactive Product');
});

test('products index applies search and category filters', function () {
    $sportsProduct = Product::factory()->create([
        'name' => 'Trail Running Shoes',
        'slug' => 'trail-running-shoes',
        'category' => 'sports',
        'is_active' => true,
    ]);

    Product::factory()->create([
        'name' => 'Kitchen Knife Set',
        'slug' => 'kitchen-knife-set',
        'category' => 'home',
        'is_active' => true,
    ]);

    $this->get(route('products.index', [
        'search' => 'trail',
        'category_id' => $sportsProduct->category_id,
    ]))
        ->assertOk()
        ->assertSee('Trail Running Shoes')
        ->assertDontSee('Kitchen Knife Set');
});

test('products index applies sorting options', function () {
    Product::factory()->create([
        'name' => 'Budget Mouse',
        'slug' => 'budget-mouse',
        'price' => 19.99,
        'is_active' => true,
    ]);

    Product::factory()->create([
        'name' => 'Premium Mouse',
        'slug' => 'premium-mouse',
        'price' => 199.99,
        'is_active' => true,
    ]);

    $this->get(route('products.index', ['sort' => 'price_desc']))
        ->assertOk()
        ->assertSeeInOrder(['Premium Mouse', 'Budget Mouse']);
});

test('products index keeps query parameters in pagination links', function () {
    $products = Product::factory()->count(13)->create([
        'category' => 'books',
        'is_active' => true,
    ]);
    $categoryId = $products->first()?->category_id;

    $this->get(route('products.index', [
        'category_id' => $categoryId,
        'sort' => 'price_asc',
        'per_page' => 12,
    ]))
        ->assertOk()
        ->assertSee('category_id='.$categoryId.'&amp;sort=price_asc&amp;per_page=12&amp;page=2', false);
});

test('products index shows empty state when filters return no products', function () {
    Product::factory()->create([
        'name' => 'Existing Product',
        'slug' => 'existing-product',
        'is_active' => true,
    ]);

    $this->get(route('products.index', ['search' => 'not-found-search-token']))
        ->assertOk()
        ->assertSee('No products match your current filters.');
});

test('home page renders hero section and product catalog', function () {
    Product::factory()->create([
        'name' => 'Home Hero Product',
        'slug' => 'home-hero-product',
        'is_active' => true,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Find smarter picks, faster.')
        ->assertSee('Product catalog')
        ->assertSee('Home Hero Product');
});

test('home recommendations use last viewed products and ai response', function () {
    config([
        'services.ai.provider' => 'openai',
        'services.ai.openai.api_key' => 'test-key',
        'services.ai.openai.model' => 'gpt-test',
    ]);

    $viewedOne = Product::factory()->create([
        'name' => 'Viewed Product One',
        'slug' => 'viewed-product-one',
        'is_active' => true,
    ]);
    $viewedTwo = Product::factory()->create([
        'name' => 'Viewed Product Two',
        'slug' => 'viewed-product-two',
        'is_active' => true,
    ]);
    $viewedThree = Product::factory()->create([
        'name' => 'Viewed Product Three',
        'slug' => 'viewed-product-three',
        'is_active' => true,
    ]);

    $recOne = Product::factory()->create([
        'name' => 'Recommended One',
        'slug' => 'recommended-one',
        'is_active' => true,
    ]);
    $recTwo = Product::factory()->create([
        'name' => 'Recommended Two',
        'slug' => 'recommended-two',
        'is_active' => true,
    ]);
    $recThree = Product::factory()->create([
        'name' => 'Recommended Three',
        'slug' => 'recommended-three',
        'is_active' => true,
    ]);

    Http::fake([
        'https://api.openai.com/*' => Http::response([
            'choices' => [[
                'message' => ['content' => json_encode([
                    ['slug' => $recOne->slug, 'name' => $recOne->name, 'price' => 11.0, 'reason' => 'match one'],
                    ['slug' => $recTwo->slug, 'name' => $recTwo->name, 'price' => 22.0, 'reason' => 'match two'],
                    ['slug' => $recThree->slug, 'name' => $recThree->name, 'price' => 33.0, 'reason' => 'match three'],
                ])],
            ]],
        ], 200),
    ]);

    $this->withSession([
        'store.viewed_products' => [$viewedOne->id, $viewedTwo->id, $viewedThree->id],
    ])->get(route('home'))
        ->assertOk()
        ->assertSee('Recommended for you')
        ->assertSee('Recommended One')
        ->assertSee('Recommended Two')
        ->assertSee('Recommended Three');
});
