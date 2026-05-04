<x-layouts.store>
    <div class="grid gap-6 lg:grid-cols-3">
        <article
            class="space-y-5 rounded-lg border border-zinc-200 bg-white p-5 lg:col-span-2 dark:border-zinc-700 dark:bg-zinc-800"
        >
            <div class="grid gap-4 md:grid-cols-2">
                <div
                    class="overflow-hidden rounded-lg border border-zinc-200 bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900">
                    <img
                        class="h-64 w-full object-cover md:h-full"
                        src="{{ $product->image }}"
                        alt="{{ $product->name }}"
                    >
                </div>

                <div class="space-y-3">
                    <x-store.status-badge
                        :label="ucfirst($product->category)"
                        tone="neutral"
                    />

                    <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $product->name }}</h1>

                    <p class="text-xl font-bold text-zinc-900 dark:text-zinc-100">
                        ${{ number_format((float) $product->price, 2) }}</p>

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

                    <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $product->description }}</p>
                </div>
            </div>

            <div class="space-y-3 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                @if (session('status'))
                    <x-store.alert type="success">
                        {{ session('status') }}
                    </x-store.alert>
                @endif
                @if ($errors->has('wishlist'))
                    <x-store.alert
                        type="danger"
                        :auto-hide="false"
                    >
                        {{ $errors->first('wishlist') }}
                    </x-store.alert>
                @endif

                @auth
                    <div class="flex items-center gap-2">
                        @if ($isWishlisted ?? false)
                            <form
                                method="POST"
                                action="{{ route('wishlist.destroy', ['product' => $product]) }}"
                            >
                                @csrf
                                @method('DELETE')
                                <button
                                    class="rounded-md border border-zinc-300 px-3 py-2 text-sm text-zinc-700 dark:border-zinc-600 dark:text-zinc-200"
                                    type="submit"
                                >
                                    {{ __('Remove from Wishlist') }}
                                </button>
                            </form>
                        @else
                            <form
                                method="POST"
                                action="{{ route('wishlist.store', ['product' => $product]) }}"
                            >
                                @csrf
                                <button
                                    class="rounded-md border border-zinc-300 px-3 py-2 text-sm text-zinc-700 dark:border-zinc-600 dark:text-zinc-200"
                                    type="submit"
                                >
                                    {{ __('Add to Wishlist') }}
                                </button>
                            </form>
                        @endif
                    </div>
                @endauth

                <livewire:store.add-to-cart-form
                    :product-id="$product->id"
                    :stock="$product->stock"
                />
            </div>
        </article>

        <x-store.recommendation-list :recommendations="$recommendations" />
    </div>
</x-layouts.store>
