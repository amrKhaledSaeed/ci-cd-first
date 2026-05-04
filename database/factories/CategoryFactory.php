<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Smpita\TypeAs\TypeAs;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = TypeAs::string(fake()->unique()->words(2, true));

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
        ];
    }
}
