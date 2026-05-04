<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Smpita\TypeAs\TypeAs;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('image')
                ->constrained('categories')
                ->nullOnDelete();
        });

        /** @var array<int, string> $categoryNames */
        $categoryNames = DB::table('products')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->map(fn (mixed $value): string => TypeAs::string($value))
            ->values()
            ->all();

        foreach ($categoryNames as $categoryName) {
            $normalizedName = Str::title($categoryName);

            DB::table('categories')->updateOrInsert(
                ['slug' => Str::slug($normalizedName)],
                [
                    'name' => $normalizedName,
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );

            $categoryId = DB::table('categories')
                ->where('slug', Str::slug($normalizedName))
                ->value('id');

            if ($categoryId !== null) {
                DB::table('products')
                    ->where('category', $categoryName)
                    ->update([
                        'category_id' => TypeAs::int($categoryId),
                    ]);
            }
        }

        $fallbackCategoryId = DB::table('categories')
            ->where('slug', 'uncategorized')
            ->value('id');

        if ($fallbackCategoryId === null) {
            DB::table('categories')->insert([
                'name' => 'Uncategorized',
                'slug' => 'uncategorized',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $fallbackCategoryId = DB::table('categories')
                ->where('slug', 'uncategorized')
                ->value('id');
        }

        if ($fallbackCategoryId !== null) {
            DB::table('products')
                ->whereNull('category_id')
                ->update([
                    'category_id' => TypeAs::int($fallbackCategoryId),
                ]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('category')->nullable()->after('image');
            $table->index('category');
        });

        $products = DB::table('products')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->select('products.id', 'categories.name as category_name')
            ->get();

        foreach ($products as $product) {
            $categoryName = TypeAs::nullableString($product->category_name) ?? 'Uncategorized';

            DB::table('products')
                ->where('id', TypeAs::int($product->id))
                ->update(['category' => Str::lower($categoryName)]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
