@props(['product', 'wishlisted' => false])

<article
    class="flex h-full flex-col gap-3 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800"
>
    <div class="flex items-start justify-between gap-3">
        <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $product->name }}</h2>
        <x-store.status-badge
            :label="ucfirst($product->category)"
            tone="neutral"
        />
    </div>

    <p class="text-sm text-zinc-600 dark:text-zinc-300">
        {{ __('Price') }}: <span class="font-medium">${{ number_format((float) $product->price, 2) }}</span>
    </p>

    <p class="text-sm">
        @if ($product->stock > 0)
            <x-store.status-badge
                :label="__('In stock') . ' (' . $product->stock . ')'"
                tone="success"
            />
        @else
            <x-store.status-badge
                :label="__('Out of stock')"
                tone="danger"
            />
        @endif
    </p>

    <div class="mt-auto flex items-center justify-between gap-2">
        <a
            class="inline-flex items-center text-sm font-medium text-zinc-900 underline underline-offset-2 dark:text-zinc-100"
            href="{{ route('products.show', ['product' => $product]) }}"
        >
            {{ __('View details') }}
        </a>

        @auth
            @if ($wishlisted)
                <form
                    method="POST"
                    action="{{ route('wishlist.destroy', ['product' => $product]) }}"
                >
                    @csrf
                    @method('DELETE')
                    <button
                        class="rounded-md border border-zinc-300 px-2 py-1 text-xs text-zinc-700 dark:border-zinc-600 dark:text-zinc-200"
                        type="submit"
                    >{{ __('Remove favorite') }}</button>
                </form>
            @else
                <form
                    method="POST"
                    action="{{ route('wishlist.store', ['product' => $product]) }}"
                >
                    @csrf
                    <button
                        class="rounded-md border border-zinc-300 px-2 py-1 text-xs text-zinc-700 dark:border-zinc-600 dark:text-zinc-200"
                        type="submit"
                    >{{ __('Add favorite') }}</button>
                </form>
            @endif
        @endauth
    </div>
</article>
