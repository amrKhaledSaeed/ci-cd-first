<x-layouts.store>
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Wishlist') }}</h1>

        @if (session('status'))
            <x-store.alert type="success">
                {{ session('status') }}
            </x-store.alert>
        @endif

        @if ($products->isEmpty())
            <x-store.alert
                type="warning"
                :auto-hide="false"
            >
                {{ __('Your wishlist is empty.') }}
            </x-store.alert>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $product)
                    <x-store.product-card
                        :product="$product"
                        :wishlisted="true"
                    />
                @endforeach
            </div>

            <div>
                {{ $products->links() }}
            </div>
        @endif
    </div>
</x-layouts.store>
