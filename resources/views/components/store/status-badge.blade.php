@props(['label', 'tone' => 'neutral'])

@php
    $classes = match ($tone) {
        'success' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
        'danger' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-200',
        'warning' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
        default => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200',
    };
@endphp

<span {{ $attributes->class(["inline-flex rounded-full px-2 py-1 text-xs {$classes}"]) }}>
    {{ $label }}
</span>
