<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Smpita\TypeAs\TypeAs;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryIdsBySlug = $this->categoryIdsBySlug();

        $catalog = [
            ['name' => 'Wireless Noise Cancelling Headphones', 'category' => 'electronics'],
            ['name' => 'Portable Bluetooth Speaker', 'category' => 'electronics'],
            ['name' => 'Smart Fitness Watch', 'category' => 'electronics'],
            ['name' => 'USB C Fast Charger', 'category' => 'electronics'],
            ['name' => 'Ergonomic Office Chair', 'category' => 'home'],
            ['name' => 'Bamboo Bedside Lamp', 'category' => 'home'],
            ['name' => 'Stainless Steel Water Bottle', 'category' => 'home'],
            ['name' => 'Minimalist Wall Clock', 'category' => 'home'],
            ['name' => 'Cotton Everyday T Shirt', 'category' => 'fashion'],
            ['name' => 'Slim Fit Denim Jeans', 'category' => 'fashion'],
            ['name' => 'Unisex Running Sneakers', 'category' => 'fashion'],
            ['name' => 'Classic Leather Wallet', 'category' => 'fashion'],
            ['name' => 'Yoga Mat Pro Grip', 'category' => 'sports'],
            ['name' => 'Adjustable Dumbbell Set', 'category' => 'sports'],
            ['name' => 'Resistance Bands Kit', 'category' => 'sports'],
            ['name' => 'Compact Foam Roller', 'category' => 'sports'],
            ['name' => 'Hydrating Face Serum', 'category' => 'beauty'],
            ['name' => 'Daily Mineral Sunscreen', 'category' => 'beauty'],
            ['name' => 'Nourishing Hair Mask', 'category' => 'beauty'],
            ['name' => 'Gentle Cleansing Gel', 'category' => 'beauty'],
            ['name' => 'Productive Deep Work Guide', 'category' => 'books'],
            ['name' => 'Modern Laravel Patterns', 'category' => 'books'],
            ['name' => 'Design Thinking Handbook', 'category' => 'books'],
            ['name' => 'Startup Finance Basics', 'category' => 'books'],
        ];

        foreach ($catalog as $index => $item) {
            $name = TypeAs::string($item['name']);
            $category = TypeAs::string($item['category']);
            $slug = Str::slug($name);
            $categorySlug = Str::slug($category);
            $categoryId = TypeAs::nullableInt($categoryIdsBySlug->get($categorySlug));

            if ($categoryId === null) {
                $categoryModel = Category::query()->firstOrCreate(
                    ['slug' => $categorySlug],
                    ['name' => Str::title($category)],
                );
                $categoryId = $categoryModel->id;
            }

            Product::factory()->create([
                'slug' => $slug,
                'name' => $name,
                'description' => 'SmartShop curated product: '.$name.'.',
                'price' => $this->deterministicPrice($index),
                'stock' => $this->deterministicStock($index),
                'image' => '/images/products/'.$slug.'.jpg',
                'category_id' => $categoryId,
                'is_active' => true,
            ]);
        }
    }

    /**
     * @return Collection<string, int>
     */
    private function categoryIdsBySlug(): Collection
    {
        if (Category::query()->count() === 0) {
            $this->call(CategorySeeder::class);
        }

        /** @var Collection<string, int> $categoryIdsBySlug */
        $categoryIdsBySlug = Category::query()
            ->pluck('id', 'slug')
            ->map(fn (mixed $id): int => TypeAs::int($id));

        return $categoryIdsBySlug;
    }

    private function deterministicPrice(int $index): float
    {
        return round(14.99 + ($index * 7.5), 2);
    }

    private function deterministicStock(int $index): int
    {
        return 8 + (($index * 3) % 70);
    }
}
