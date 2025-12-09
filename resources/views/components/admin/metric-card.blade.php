@props([
    'label' => '',
    'value' => '',
    'helper' => null,
    'icon' => 'document-text',
    'tone' => 'blue',
])

@php
    $tones = [
        'blue' => ['bg' => 'bg-blue-500/15 dark:bg-blue-400/15', 'text' => 'text-blue-600 dark:text-blue-300'],
        'amber' => ['bg' => 'bg-amber-500/15 dark:bg-amber-400/15', 'text' => 'text-amber-600 dark:text-amber-200'],
        'rose' => ['bg' => 'bg-rose-500/15 dark:bg-rose-400/15', 'text' => 'text-rose-600 dark:text-rose-200'],
        'emerald' => ['bg' => 'bg-emerald-500/15 dark:bg-emerald-400/15', 'text' => 'text-emerald-600 dark:text-emerald-200'],
        'slate' => ['bg' => 'bg-slate-500/15 dark:bg-slate-400/15', 'text' => 'text-slate-600 dark:text-slate-200'],
    ];
    $palette = $tones[$tone] ?? $tones['blue'];
    $iconComponent = 'heroicon-o-' . trim($icon);
@endphp

<div class="flex items-start gap-3 rounded-2xl border border-white/30/0 dark:border-gray-700/60 bg-white/70 dark:bg-gray-800/70 p-4 shadow-sm">
    <div class="flex h-11 w-11 items-center justify-center rounded-xl {{ $palette['bg'] }} {{ $palette['text'] }}">
        <x-dynamic-component :component="$iconComponent" class="w-5 h-5" />
    </div>
    <div class="space-y-1">
        <p class="text-[11px] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">{{ $label }}</p>
        <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $value }}</p>
        @if($helper)
            <p class="text-[11px] text-gray-500 dark:text-gray-400">{{ $helper }}</p>
        @endif
    </div>
</div>
