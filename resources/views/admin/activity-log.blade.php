@extends('layouts.admin')

@section('title', 'Activity Log')

@section('content')
<div class="w-full mx-auto px-4 sm:px-6 py-4 sm:py-6 lg:py-8 font-[Poppins] space-y-6 lg:space-y-8">
    <div class="flex items-center justify-between mb-3">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Activity Log</h1>
        <button type="button" id="toggleFilters"
                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 text-xs font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
            <x-heroicon-o-funnel class="w-4 h-4" />
            <span>Filters</span>
        </button>
    </div>

    @php
        $filtersActive = ($module || $action || $userId || $q || $dateFrom || $dateTo);
    @endphp

    <!-- Filters -->
    <form method="GET" id="filtersPanel" class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-4 lg:p-5 space-y-4 {{ $filtersActive ? '' : 'hidden' }}">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Module</label>
                <select name="module" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100">
                    <option value="">All modules</option>
                    @foreach($modules as $m)
                        <option value="{{ $m }}" {{ $module === $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Action</label>
                <select name="action" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100">
                    <option value="">All actions</option>
                    @foreach($actions as $a)
                        <option value="{{ $a }}" {{ $action === $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">User</label>
                <select name="user_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100">
                    <option value="">All users</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ (string)$userId === (string)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Search</label>
                <input type="text" name="q" value="{{ $q }}" placeholder="Account, description..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100" />
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">From</label>
                <input type="date" name="from" value="{{ $dateFrom }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100" />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">To</label>
                <input type="date" name="to" value="{{ $dateTo }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100" />
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium w-full md:w-auto">Apply filters</button>
                <a href="{{ route('admin.activity-log') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium w-full md:w-auto text-center">Reset</a>
            </div>
        </div>
    </form>

    <!-- Activity table -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Recent activity</h2>
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $logs->total() }} entries</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-2 text-left">Time</th>
                        <th class="px-4 py-2 text-left">User</th>
                        <th class="px-4 py-2 text-left">Module</th>
                        <th class="px-4 py-2 text-left">Action</th>
                        <th class="px-4 py-2 text-left">Description</th>
                        <th class="px-4 py-2 text-left">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-100">
                    @forelse($logs as $log)
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-xs md:text-sm">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-xs md:text-sm">{{ $log->user->name ?? 'System' }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-xs md:text-sm">{{ $log->module }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-xs md:text-sm">{{ $log->action }}</td>
                            <td class="px-4 py-2 text-xs md:text-sm max-w-md truncate" title="{{ $log->description }}">{{ $log->description }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-xs md:text-sm">
                                @if($log->meta)
                                    <button type="button" onclick="showActivityMeta({{ $log->id }})" class="px-3 py-1 rounded-md text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600">View</button>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400 text-sm">No activity found for the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
            {{ $logs->links() }}
        </div>
    </div>
</div>

<script>
@if($logs->count())
    window.__activityMeta = @json($logs->keyBy('id')->map->meta);
    function showActivityMeta(id){
        const data = window.__activityMeta[id] || null;
        if (!data) { alert('No additional details.'); return; }
        alert(JSON.stringify(data, null, 2));
    }
@else
    function showActivityMeta(id){
        alert('No additional details.');
    }
@endif

    document.addEventListener('DOMContentLoaded', function(){
        var btn = document.getElementById('toggleFilters');
        var panel = document.getElementById('filtersPanel');
        if (!btn || !panel) return;
        btn.addEventListener('click', function(){
            var hidden = panel.classList.contains('hidden');
            panel.classList.toggle('hidden', !hidden);
        });
    });
</script>
@endsection
