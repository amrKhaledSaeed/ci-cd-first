@props(['line'])

<tr>
    <td class="px-4 py-3">
        <a
            class="font-medium text-zinc-900 underline underline-offset-2 dark:text-zinc-100"
            href="{{ route('products.show', ['product' => $line['slug']]) }}"
        >
            {{ $line['name'] }}
        </a>
    </td>
    <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">${{ number_format((float) $line['unit_price'], 2) }}</td>
    <td class="px-4 py-3">
        <form
            class="flex items-center gap-2"
            method="POST"
            action="{{ route('cart.update', ['productId' => $line['product_id']]) }}"
            x-data="{ quantity: {{ (int) $line['quantity'] }} }"
        >
            @csrf
            @method('PATCH')
            <button
                class="rounded-md border border-zinc-300 px-2 py-1 text-zinc-700 dark:border-zinc-600 dark:text-zinc-200"
                type="button"
                @click="quantity = Math.max(0, quantity - 1)"
            >-</button>
            <input
                class="w-20 rounded-md border border-zinc-300 bg-white px-2 py-1 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100"
                id="qty-{{ $line['product_id'] }}"
                name="quantity"
                type="number"
                value="{{ (int) $line['quantity'] }}"
                min="0"
                x-model.number="quantity"
            >
            <button
                class="rounded-md border border-zinc-300 px-2 py-1 text-zinc-700 dark:border-zinc-600 dark:text-zinc-200"
                type="button"
                @click="quantity += 1"
            >+</button>
            <button
                class="rounded-md border border-zinc-300 px-2 py-1 text-zinc-700 dark:border-zinc-600 dark:text-zinc-200"
                type="submit"
            >
                {{ __('Update') }}
            </button>
        </form>
    </td>
    <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">${{ number_format((float) $line['subtotal'], 2) }}</td>
    <td class="px-4 py-3">
        <form
            method="POST"
            action="{{ route('cart.destroy', ['productId' => $line['product_id']]) }}"
        >
            @csrf
            @method('DELETE')
            <button
                class="rounded-md border border-zinc-900 bg-zinc-900 px-2 py-1 text-white hover:bg-zinc-700 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-300"
                type="submit"
            >
                {{ __('Remove') }}
            </button>
        </form>
    </td>
</tr>
