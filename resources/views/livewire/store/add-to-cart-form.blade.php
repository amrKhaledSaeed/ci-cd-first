<div class="space-y-3">
    <form
        class="flex flex-wrap items-end gap-3"
        method="POST"
        action="{{ route('cart.store') }}"
        wire:submit="addToCart"
        novalidate
    >
        @csrf
        <input
            type="hidden"
            name="product_id"
            value="{{ $productId }}"
        >

        <div class="flex flex-col gap-1">
            <label
                class="text-sm text-zinc-700 dark:text-zinc-200"
                for="quantity"
            >{{ __('Quantity') }}</label>

            <div class="flex items-center gap-2">
                <button
                    class="rounded-md border border-zinc-300 px-2 py-2 text-zinc-700 disabled:cursor-not-allowed disabled:opacity-60 dark:border-zinc-600 dark:text-zinc-200"
                    type="button"
                    onclick="const input=this.parentElement.querySelector('input[name=quantity]'); const min=Number(input.min)||1; input.value=Math.max(min, Number(input.value||min)-1); input.dispatchEvent(new Event('input',{bubbles:true}));"
                    wire:loading.attr="disabled"
                    @disabled($stock < 1)
                >-</button>
                <input
                    class="w-24 rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 disabled:cursor-not-allowed disabled:opacity-60 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                    id="quantity"
                    type="number"
                    name="quantity"
                    min="1"
                    max="{{ max($stock, 1) }}"
                    wire:model="quantity"
                    wire:loading.attr="disabled"
                    @disabled($stock < 1)
                >
                <button
                    class="rounded-md border border-zinc-300 px-2 py-2 text-zinc-700 disabled:cursor-not-allowed disabled:opacity-60 dark:border-zinc-600 dark:text-zinc-200"
                    type="button"
                    onclick="const input=this.parentElement.querySelector('input[name=quantity]'); const max=Number(input.max)||1; const min=Number(input.min)||1; input.value=Math.min(max, Math.max(min, Number(input.value||min)+1)); input.dispatchEvent(new Event('input',{bubbles:true}));"
                    wire:loading.attr="disabled"
                    @disabled($stock < 1)
                >+</button>
            </div>
        </div>

        <button
            class="rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-60 dark:bg-zinc-100 dark:text-zinc-900"
            type="submit"
            wire:loading.attr="disabled"
            @disabled($stock < 1)
        >
            <span wire:loading.remove wire:target="addToCart">{{ __('Add to Cart') }}</span>
            <span wire:loading wire:target="addToCart">{{ __('Adding...') }}</span>
        </button>
    </form>

    @if ($statusMessage)
        <x-store.alert type="success">
            {{ $statusMessage }}
        </x-store.alert>
    @endif

    @error('quantity')
        <x-store.alert
            type="danger"
            :auto-hide="false"
        >
            {{ $message }}
        </x-store.alert>
    @enderror
</div>
