<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Smpita\TypeAs\TypeAs;

class Product extends Model
{
    /** @use \Illuminate\Database\Eloquent\Factories\HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, LogsModelActivity;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * @return HasMany<CartItem, $this>
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return HasMany<StoreAnalyticsEvent, $this>
     */
    public function storeAnalyticsEvents(): HasMany
    {
        return $this->hasMany(StoreAnalyticsEvent::class);
    }

    /**
     * @return HasMany<WishlistItem, $this>
     */
    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function setCategoryAttribute(?string $categoryName): void
    {
        if ($categoryName === null || mb_trim($categoryName) === '') {
            $this->attributes['category_id'] = null;

            return;
        }

        $normalizedName = Str::title(TypeAs::string($categoryName));
        $slug = Str::slug($normalizedName);

        $category = Category::query()->firstOrCreate(
            ['slug' => $slug],
            ['name' => $normalizedName],
        );

        $this->attributes['category_id'] = $category->id;
    }

    public function getCategoryAttribute(): ?string
    {
        if ($this->relationLoaded('productCategory')) {
            return $this->productCategory?->name;
        }

        return $this->productCategory()->value('name');
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'category_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
