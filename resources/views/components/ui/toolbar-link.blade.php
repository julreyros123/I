@props([
    'href' => '#',
])
<a href="{{ $href }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100/70 dark:hover:bg-gray-700/60 px-2 py-1 rounded transition-colors duration-200']) }}>
    {{ $slot }}
</a>
