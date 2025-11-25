@props([
    'title' => '',
    'value' => '',
])
<div class="rounded-xl shadow p-5">
    <div class="flex items-center">
        <div class="flex-shrink-0 flex items-center justify-center w-10 h-10 bg-transparent rounded-lg ring-1 ring-white/5">
            {{ $icon ?? '' }}
        </div>
        <div class="ml-4 min-w-0 flex-1">
            <p class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ $title }}</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white mt-0.5">{{ $value }}</p>
        </div>
    </div>
</div>
