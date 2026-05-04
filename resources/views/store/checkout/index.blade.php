<x-layouts.store>
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Checkout') }}</h1>

        @if ($errors->has('cart'))
            <x-store.alert
                type="danger"
                :auto-hide="false"
            >
                {{ $errors->first('cart') }}
            </x-store.alert>
        @endif
        @if ($errors->has('checkout'))
            <x-store.alert
                type="warning"
                :auto-hide="false"
            >
                {{ $errors->first('checkout') }}
            </x-store.alert>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <section
                class="space-y-4 rounded-lg border border-zinc-200 bg-white p-5 lg:col-span-2 dark:border-zinc-700 dark:bg-zinc-800"
            >
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Checkout Details') }}</h2>

                <form
                    class="grid gap-4"
                    method="POST"
                    action="{{ route('checkout.store') }}"
                    onsubmit="const button=this.querySelector('[data-submit]'); if (button) { button.disabled = true; const label=button.querySelector('[data-label]'); if (label) { label.textContent='{{ __('Processing...') }}'; } }"
                >
                    @csrf

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-1">
                            <label
                                class="text-sm text-zinc-700 dark:text-zinc-200"
                                for="customer_name"
                            >{{ __('Full Name') }}</label>
                            <input
                                class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                                id="customer_name"
                                name="customer_name"
                                type="text"
                                value="{{ old('customer_name') }}"
                            >
                            @error('customer_name')
                                <p class="text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1">
                            <label
                                class="text-sm text-zinc-700 dark:text-zinc-200"
                                for="customer_email"
                            >{{ __('Email') }}</label>
                            <input
                                class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                                id="customer_email"
                                name="customer_email"
                                type="email"
                                value="{{ old('customer_email') }}"
                            >
                            @error('customer_email')
                                <p class="text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label
                            class="text-sm text-zinc-700 dark:text-zinc-200"
                            for="shipping_address"
                        >{{ __('Shipping Address') }}</label>
                        <textarea
                            class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                            id="shipping_address"
                            name="shipping_address"
                            rows="3"
                        >{{ old('shipping_address') }}</textarea>
                        @error('shipping_address')
                            <p class="text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1">
                        <label
                            class="text-sm text-zinc-700 dark:text-zinc-200"
                            for="payment_method"
                        >{{ __('Payment Method') }}</label>
                        <select
                            class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                            id="payment_method"
                            name="payment_method"
                        >
                            <option value="">{{ __('Choose a method') }}</option>
                            <option
                                value="card"
                                @selected(old('payment_method') === 'card')
                            >{{ __('Card') }}</option>
                            <option
                                value="cod"
                                @selected(old('payment_method') === 'cod')
                            >{{ __('Cash on Delivery') }}</option>
                            <option
                                value="wallet"
                                @selected(old('payment_method') === 'wallet')
                            >{{ __('Wallet') }}</option>
                        </select>
                        @error('payment_method')
                            <p class="text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        class="rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-60 dark:bg-zinc-100 dark:text-zinc-900"
                        data-submit
                        type="submit"
                        @disabled($cart['meta']['items_count'] < 1)
                    >
                        <span data-label>{{ __('Simulate Checkout') }}</span>
                    </button>
                </form>
            </section>

            <aside
                class="space-y-3 rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800"
            >
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Order Summary') }}</h2>
                <p class="text-sm text-zinc-700 dark:text-zinc-300">
                    {{ __('Items') }}: {{ $cart['meta']['items_count'] }}
                </p>
                <p class="text-sm text-zinc-700 dark:text-zinc-300">
                    {{ __('Subtotal') }}: ${{ number_format((float) $cart['meta']['subtotal'], 2) }}
                </p>
                @if ($cart['meta']['items_count'] < 1)
                    <x-store.status-badge
                        :label="__('Add items to cart before checkout.')"
                        tone="warning"
                    />
                @endif
            </aside>
        </div>
    </div>
</x-layouts.store>
