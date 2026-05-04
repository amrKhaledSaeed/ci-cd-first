<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Smpita\TypeAs\TypeAs;

class CategorySeeder extends Seeder
{
    /**
     * @return array<int, string>
     */
    public static function catalog(): array
    {
        return [
            'electronics',
            'home',
            'fashion',
            'sports',
            'beauty',
            'books',
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::catalog() as $name) {
            $normalizedName = Str::title(TypeAs::string($name));

            Category::query()->updateOrCreate(
                ['slug' => Str::slug($normalizedName)],
                ['name' => $normalizedName],
            );
        }
    }
}
