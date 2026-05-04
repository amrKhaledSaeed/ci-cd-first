<x-layouts.store>
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Products') }}</h1>

        @if ($errors->has('product'))
            <x-store.alert
                type="warning"
                :auto-hide="false"
            >
                {{ $errors->first('product') }}
            </x-store.alert>
        @endif

        <form
            class="grid gap-3 rounded-lg border border-zinc-200 bg-white p-4 md:grid-cols-4 dark:border-zinc-700 dark:bg-zinc-800"
            method="GET"
            action="{{ route('products.index') }}"
        >
            <input
                class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                name="search"
                type="text"
                value="{{ $filters['search'] }}"
                placeholder="{{ __('Search products') }}"
            />

            <select
                class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                name="category_id"
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
                    class="rounded-md bg-zinc-900 px-3 py-2 text-sm font-medium text-white dark:bg-zinc-100 dark:text-zinc-900"
                    type="submit"
                >
                    {{ __('Apply') }}
                </button>
                <a
                    class="rounded-md border border-zinc-300 px-3 py-2 text-sm text-zinc-700 dark:border-zinc-600 dark:text-zinc-200"
                    href="{{ route('products.index') }}"
                >
                    {{ __('Reset') }}
                </a>
            </div>
        </form>

        <p class="text-sm text-zinc-600 dark:text-zinc-300">
            {{ __('Loaded products') }}: {{ $products->total() }}
        </p>

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
    </div>
</x-layouts.store>
