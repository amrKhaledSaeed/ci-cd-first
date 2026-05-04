<div class="space-y-6">
    @if (session('status'))
        <x-store.alert type="success">
            {{ session('status') }}
        </x-store.alert>
    @endif

    @if ($statusMessage)
        <x-store.alert type="success">
            {{ $statusMessage }}
        </x-store.alert>
    @endif

    @if (($cart['meta']['stale_removed_count'] ?? 0) > 0)
        <x-store.alert type="warning">
            {{ __('Some cart items were adjusted or removed because they are no longer available.') }}
        </x-store.alert>
    @endif

    @if ($cart['lines'] === [])
        <x-store.alert
            class="rounded-lg border border-dashed border-zinc-300 bg-zinc-50 p-8 text-center text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
            type="warning"
            :auto-hide="false"
        >
            {{ __('Your cart is empty.') }}
        </x-store.alert>
    @else
        @php
            $displayItemsCount = collect($cart['lines'])->sum(
                fn(array $line): int => (int) ($quantities[$line['product_id']] ?? $line['quantity']),
            );
            $displaySubtotal = collect($cart['lines'])->sum(
                fn(array $line): float => ((float) ($line['unit_price'] ?? 0)) * ((int) ($quantities[$line['product_id']] ?? $line['quantity'])),
            );
        @endphp
        <div class="space-y-4">
            <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-900/60">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-zinc-700 dark:text-zinc-200">{{ __('Product') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-700 dark:text-zinc-200">{{ __('Unit Price') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-700 dark:text-zinc-200">{{ __('Quantity') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-700 dark:text-zinc-200">{{ __('Subtotal') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-700 dark:text-zinc-200">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach ($cart['lines'] as $line)
                            <tr wire:key="cart-line-{{ $line['product_id'] }}">
                                <td class="px-4 py-3">
                                    <a
                                        class="font-medium text-zinc-900 underline underline-offset-2 dark:text-zinc-100"
                                        href="{{ route('products.show', ['product' => $line['slug']]) }}"
                                    >
                                        {{ $line['name'] }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">
                                    ${{ number_format((float) $line['unit_price'], 2) }}
                                </td>
                                <td class="px-4 py-3">
                                    <form
                                        class="flex items-center gap-2"
                                        wire:submit="updateLine({{ $line['product_id'] }})"
                                    >
                                        <button
                                            class="rounded-md border border-zinc-300 px-2 py-1 text-zinc-700 dark:border-zinc-600 dark:text-zinc-200"
                                            type="button"
                                            onclick="const input=this.parentElement.querySelector('input[name=quantity]'); const min=Number(input.min)||0; input.value=Math.max(min, Number(input.value||min)-1); input.dispatchEvent(new Event('input',{bubbles:true}));"
                                        >-</button>
                                        <input
                                            class="w-20 rounded-md border border-zinc-300 bg-white px-2 py-1 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                                            type="number"
                                            name="quantity"
                                            min="0"
                                            wire:model.live="quantities.{{ $line['product_id'] }}"
                                        >
                                        <button
                                            class="rounded-md border border-zinc-300 px-2 py-1 text-zinc-700 dark:border-zinc-600 dark:text-zinc-200"
                                            type="button"
                                            onclick="const input=this.parentElement.querySelector('input[name=quantity]'); const min=Number(input.min)||0; input.value=Math.max(min, Number(input.value||min)+1); input.dispatchEvent(new Event('input',{bubbles:true}));"
                                        >+</button>
                                        <button
                                            class="rounded-md border border-zinc-300 px-2 py-1 text-zinc-700 dark:border-zinc-600 dark:text-zinc-200"
                                            type="submit"
                                            wire:loading.attr="disabled"
                                            wire:target="updateLine({{ $line['product_id'] }})"
                                        >
                                            <span wire:loading.remove wire:target="updateLine({{ $line['product_id'] }})">{{ __('Update') }}</span>
                                            <span wire:loading wire:target="updateLine({{ $line['product_id'] }})">{{ __('Updating...') }}</span>
                                        </button>
                                    </form>
                                    @error('quantities.'.$line['product_id'])
                                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">
                                    ${{ number_format(((float) ($line['unit_price'] ?? 0)) * ((int) ($quantities[$line['product_id']] ?? $line['quantity'])), 2) }}
                                </td>
                                <td class="px-4 py-3">
                                    <button
                                        class="rounded-md border border-zinc-900 bg-zinc-900 px-2 py-1 text-white hover:bg-zinc-700 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-300"
                                        type="button"
                                        wire:click="removeLine({{ $line['product_id'] }})"
                                        wire:loading.attr="disabled"
                                        wire:target="removeLine({{ $line['product_id'] }})"
                                    >
                                        <span wire:loading.remove wire:target="removeLine({{ $line['product_id'] }})">{{ __('Remove') }}</span>
                                        <span wire:loading wire:target="removeLine({{ $line['product_id'] }})">{{ __('Removing...') }}</span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="text-sm text-zinc-700 dark:text-zinc-300">
                    {{ __('Items') }}: <span class="font-medium">{{ $displayItemsCount }}</span>
                    <span class="mx-2">|</span>
                    {{ __('Subtotal') }}: <span class="font-medium">${{ number_format($displaySubtotal, 2) }}</span>
                </div>

                <div class="flex items-center gap-2">
                    @auth
                        <a
                            class="rounded-md bg-zinc-900 px-3 py-2 text-sm text-white dark:bg-zinc-100 dark:text-zinc-900"
                            href="{{ route('checkout.index') }}"
                        >{{ __('Proceed to Checkout') }}</a>
                    @else
                        <a
                            class="rounded-md bg-zinc-900 px-3 py-2 text-sm text-white dark:bg-zinc-100 dark:text-zinc-900"
                            href="{{ route('login') }}"
                        >{{ __('Login to Checkout') }}</a>
                    @endauth

                    <button
                        class="rounded-md border border-zinc-300 px-3 py-2 text-sm text-zinc-700 disabled:cursor-not-allowed disabled:opacity-60 dark:border-zinc-600 dark:text-zinc-200"
                        type="button"
                        wire:click="clearCart"
                        wire:loading.attr="disabled"
                        wire:target="clearCart"
                    >
                        <span wire:loading.remove wire:target="clearCart">{{ __('Clear cart') }}</span>
                        <span wire:loading wire:target="clearCart">{{ __('Clearing...') }}</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
