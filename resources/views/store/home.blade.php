<div class="space-y-8">
        <section
            class="rounded-xl border border-amber-200 bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 p-6 text-white shadow-sm dark:border-zinc-700 dark:from-amber-700 dark:via-orange-700 dark:to-rose-700"
        >
            <p class="text-xs uppercase tracking-[0.2em] text-zinc-900 dark:text-zinc-100">{{ __('SmartShop Mini') }}</p>
            <div class="mt-3 max-w-3xl space-y-2">
                <h1 class="rounded-lg bg-emerald-950/80 px-3 py-2 text-sm text-zinc-900 backdrop-blur-sm">
                    {{ __('Find smarter picks, faster.') }}
                </h1>
                <p class="rounded-lg bg-emerald-950/80 px-3 py-2 text-sm text-zinc-900 backdrop-blur-sm">
                    {{ __('Browse the catalog and get AI-powered product suggestions based on what you recently viewed.') }}
                </p>
            </div>
            <div class="mt-4 flex items-center gap-2 text-xs text-zinc-900 dark:text-zinc-100">
                <span class="rounded-full bg-white/70 px-2 py-1 text-zinc-900 ring-1 ring-black/10 dark:bg-black/30 dark:text-zinc-100 dark:ring-white/20">{{ __('Session cart') }}</span>
                <span
                    class="rounded-full bg-white/70 px-2 py-1 text-zinc-900 ring-1 ring-black/10 dark:bg-black/30 dark:text-zinc-100 dark:ring-white/20">{{ __('AI recommendations') }}</span>
                <span
                    class="rounded-full bg-white/70 px-2 py-1 text-zinc-900 ring-1 ring-black/10 dark:bg-black/30 dark:text-zinc-100 dark:ring-white/20">{{ __('Checkout simulation') }}</span>
            </div>
        </section>

        <section class="space-y-4">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Recommended for you') }}</h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    {{ __('Source') }}: {{ $recommendations['source'] ?? 'fallback' }}
                </p>
            </div>

            @if (($recommendations['items'] ?? []) === [])
                <x-store.alert type="warning">
                    {{ __('Recommendations are temporarily unavailable.') }}
                </x-store.alert>
            @else
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach ($recommendations['items'] as $item)
                        <article
                            class="space-y-2 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800"
                        >
                            <a
                                class="text-sm font-semibold text-zinc-900 underline underline-offset-2 dark:text-zinc-100"
                                href="{{ route('products.show', ['product' => $item['slug']]) }}"
                            >
                                {{ $item['name'] }}
                            </a>
                            <p class="text-sm text-zinc-600 dark:text-zinc-300">
                                ${{ number_format((float) $item['price'], 2) }}
                            </p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ __('Why this is suggested') }}: {{ $item['reason'] }}
                            </p>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="space-y-4">
            <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Product catalog') }}</h2>

            <div
                class="grid gap-3 rounded-lg border border-zinc-200 bg-white p-4 md:grid-cols-4 dark:border-zinc-700 dark:bg-zinc-800"
            >
                <input
                    class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                    name="search"
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('Search products') }}"
                />

                <select
                    class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                    name="category_id"
                    wire:model.live="categoryId"
                >
                    <option value="">{{ __('All categories') }}</option>
                    @foreach ($categories as $category)
                        <option
                            value="{{ $category->id }}"
                            @selected($filters['category_id'] === $category->id)
                        >{{ $category->name }}</option>
                    @endforeach
                </select>

                <select
                    class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                    name="sort"
                    wire:model.live="sort"
                >
                    <option
                        value="relevance"
                        @selected($filters['sort'] === 'relevance')
                    >{{ __('Relevance') }}</option>
                    <option
                        value="price_asc"
                        @selected($filters['sort'] === 'price_asc')
                    >{{ __('Price: Low to High') }}</option>
                    <option
                        value="price_desc"
                        @selected($filters['sort'] === 'price_desc')
                    >{{ __('Price: High to Low') }}</option>
                    <option
                        value="newest"
                        @selected($filters['sort'] === 'newest')
                    >{{ __('Newest') }}</option>
                </select>

                <div class="flex items-center gap-2">
                    <button
                        class="rounded-md border border-zinc-300 px-3 py-2 text-sm text-zinc-700 dark:border-zinc-600 dark:text-zinc-200"
                        type="button"
                        wire:click="resetFilters"
                    >
                        {{ __('Reset') }}
                    </button>
                </div>
            </div>

            @if ($products->isEmpty())
                <x-store.alert type="warning">
                    {{ __('No products match your current filters.') }}
                </x-store.alert>
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($products as $product)
                        <x-store.product-card
                            :product="$product"
                            :wishlisted="in_array($product->id, $wishlistProductIds ?? [], true)"
                        />
                    @endforeach
                </div>

                <div>
                    {{ $products->links() }}
                </div>
            @endif
        </section>
</div>
