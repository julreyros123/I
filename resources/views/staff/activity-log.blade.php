@extends('layouts.app')

@section('content')
<div class="flex">
    <div class="flex-1 p-8 font-[Poppins] transition-colors duration-300">
        <div class="p-2 w-full space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Staff Activity Log</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">A complete feed of billing, payment, and registration activity.</p>
                </div>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-300 dark:hover:text-blue-200">
                    <x-heroicon-o-arrow-uturn-left class="w-4 h-4" />
                    <span>Back to dashboard</span>
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow">
                <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-gray-200">Recent Activity</h2>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ count($activityLog) }} entries</span>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($activityLog as $item)
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3 px-5 py-3">
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                @switch($item['type'] ?? null)
                                    @case('bill')
                                        <x-heroicon-o-document-text class="w-5 h-5 text-blue-500" />
                                        @break
                                    @case('payment')
                                        <x-heroicon-o-banknotes class="w-5 h-5 text-emerald-500" />
                                        @break
                                    @case('registration')
                                        <x-heroicon-o-user-plus class="w-5 h-5 text-indigo-500" />
                                        @break
                                    @default
                                        <x-heroicon-o-bell class="w-5 h-5 text-gray-400" />
                                @endswitch
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate">{{ $item['message'] ?? 'Activity' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item['time'] ?? '' }}</p>
                                </div>
                            </div>
                            <span class="text-xs font-medium uppercase tracking-wide px-2 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-200">
                                {{ ucfirst($item['type'] ?? 'Activity') }}
                            </span>
                        </div>
                    @empty
                        <div class="px-5 py-6 text-sm text-gray-500 dark:text-gray-400 text-center">No activity recorded yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
