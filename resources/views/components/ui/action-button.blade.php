@props([
    'variant' => 'neutral',
    'size' => 'sm',
])

@php
    $base = 'inline-flex items-center justify-center font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed';

    $sizes = [
        'xs' => 'h-7 px-2.5 text-xs rounded-lg',
        'sm' => 'h-9 px-3.5 text-sm rounded-xl',
        'md' => 'h-10 px-4 text-sm rounded-xl',
    ];

    $variants = [
        'primary' => 'bg-blue-600 text-white hover:bg-blue-500 focus:ring-blue-500',
        'info' => 'bg-sky-600 text-white hover:bg-sky-500 focus:ring-sky-500',
        'success' => 'bg-emerald-600 text-white hover:bg-emerald-500 focus:ring-emerald-500',
        'warning' => 'bg-amber-500 text-white hover:bg-amber-400 focus:ring-amber-500',
        'danger' => 'bg-rose-600 text-white hover:bg-rose-500 focus:ring-rose-500',
        'neutral' => 'bg-gray-100 text-gray-800 hover:bg-gray-200 focus:ring-gray-400 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700',
        'ghost' => 'border border-gray-300 text-gray-700 hover:bg-gray-100 focus:ring-gray-400 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['sm'];
    $variantClass = $variants[$variant] ?? $variants['neutral'];
@endphp

<button {{ $attributes->merge(['type' => 'button'])->class($base.' '.$sizeClass.' '.$variantClass) }}>
    {{ $slot }}
</button>
