@props([
    'type' => 'info',
    'autoHide' => true,
])

@php
    $classes = match ($type) {
        'success' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
        'danger' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-200',
        'warning' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
        default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-100',
    };
@endphp

<div
    x-data="{ visible: true }"
    @if ($autoHide) x-init="setTimeout(() => visible = false, 3500)" @endif
    x-show="visible"
    x-transition.opacity
    {{ $attributes->class(["rounded-md px-3 py-2 text-sm {$classes}"]) }}
>
    {{ $slot }}
</div>
