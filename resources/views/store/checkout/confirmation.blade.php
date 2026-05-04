<x-layouts.store>
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Checkout Confirmation') }}</h1>

        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-zinc-700 dark:text-zinc-300">
                {{ __('Confirmation code') }}:
                <span
                    class="font-mono font-semibold text-zinc-900 dark:text-zinc-100">{{ $simulation['confirmation_code'] ?? '-' }}</span>
            </p>
            <p class="text-zinc-700 dark:text-zinc-300">
                {{ __('Processed at') }}: {{ $simulation['processed_at'] ?? '-' }}
            </p>
            <p class="text-zinc-700 dark:text-zinc-300">
                {{ __('Customer') }}: {{ $simulation['customer']['name'] ?? '-' }}
                ({{ $simulation['customer']['email'] ?? '-' }})
            </p>
            <p class="text-zinc-700 dark:text-zinc-300">
                {{ __('Payment') }}: {{ strtoupper((string) ($simulation['shipping']['payment_method'] ?? '-')) }}
            </p>
            <p class="text-zinc-700 dark:text-zinc-300">
                {{ __('Subtotal') }}: ${{ number_format((float) ($simulation['totals']['subtotal'] ?? 0), 2) }}
            </p>
            <p class="text-zinc-700 dark:text-zinc-300">
                {{ __('Grand Total') }}: ${{ number_format((float) ($simulation['totals']['grand_total'] ?? 0), 2) }}
            </p>
        </div>

        <div class="space-y-3 rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Line Snapshot') }}</h2>
            @if (!empty($simulation['line_snapshot']))
                <ul class="space-y-2">
                    @foreach ($simulation['line_snapshot'] as $line)
                        <li
                            class="flex flex-wrap items-center justify-between gap-3 rounded-md border border-zinc-200 p-3 dark:border-zinc-700">
                            <span class="text-zinc-900 dark:text-zinc-100">{{ $line['name'] }}</span>
                            <span class="text-sm text-zinc-600 dark:text-zinc-300">
                                {{ __('Qty') }}: {{ $line['quantity'] }} |
                                ${{ number_format((float) $line['subtotal'], 2) }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('No line snapshot available.') }}</p>
            @endif
        </div>
    </div>
</x-layouts.store>
