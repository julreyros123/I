@props([
    'name' => null,
    'value' => null,
    'rows' => 3,
])
<textarea
    @if($name) name="{{ $name }}" @endif
    rows="{{ $rows }}"
    {{ $attributes->merge(['class' => 'w-full px-4 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500 hover:border-gray-300 dark:hover:border-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-colors']) }}
>{{ $value ?? $slot }}</textarea>
