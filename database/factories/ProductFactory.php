<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Smpita\TypeAs\TypeAs;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = TypeAs::string(fake()->unique()->words(3, true));
        $slug = Str::slug($name);

        return [
            'slug' => $slug,
            'name' => Str::title($name),
            'description' => fake()->sentence(18),
            'price' => fake()->randomFloat(2, 9, 499),
            'stock' => fake()->numberBetween(0, 120),
            'image' => '/images/products/'.$slug.'.jpg',
            'category_id' => Category::factory(),
            'is_active' => true,
        ];
    }
}
