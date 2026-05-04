@props([
    'recommendations' => [],
])

<aside class="space-y-3 rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
    <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Recommended for you') }}</h2>

    @if (!empty($recommendations['items']))
        <ul class="space-y-3">
            @foreach ($recommendations['items'] as $item)
                <li class="rounded-md border border-zinc-200 p-3 dark:border-zinc-700">
                    <a
                        class="font-medium text-zinc-900 underline underline-offset-2 dark:text-zinc-100"
                        href="{{ route('products.show', ['product' => $item['slug']]) }}"
                    >
                        {{ $item['name'] }}
                    </a>
                    <p class="text-sm text-zinc-600 dark:text-zinc-300">${{ number_format((float) $item['price'], 2) }}
                    </p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        {{ __('Why this is suggested') }}: {{ $item['reason'] }}
                    </p>
                </li>
            @endforeach
        </ul>
    @else
        <p class="text-sm text-zinc-600 dark:text-zinc-300">
            {{ __('Recommendations are temporarily unavailable. Showing fallback suggestions when possible.') }}
        </p>
    @endif

    <p class="text-xs text-zinc-500 dark:text-zinc-400">
        {{ __('Source') }}: {{ $recommendations['source'] ?? 'fallback' }}
    </p>
</aside>
