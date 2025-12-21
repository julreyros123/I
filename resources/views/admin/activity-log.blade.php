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
        $displayUserName = function ($user = null) {
            $name = trim(optional($user)->name ?? '');
            if ($name === '') {
                return 'System';
            }
            return strcasecmp($name, 'Sample Staff') === 0 ? 'Staff' : $name;
        };
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
                            <td class="px-4 py-2 whitespace-nowrap text-xs md:text-sm">{{ $displayUserName($log->user ?? null) }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-xs md:text-sm">{{ $log->module }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-xs md:text-sm">{{ $log->action }}</td>
                            <td class="px-4 py-2 text-xs md:text-sm max-w-md truncate" title="{{ $log->description }}">{{ $log->description }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-xs md:text-sm">
                                <button type="button" onclick="showActivityMeta({{ $log->id }})" class="px-3 py-1 rounded-md text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600">View</button>
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

<div id="activityDetailModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-sm">
    <div class="absolute inset-0" onclick="closeActivityMeta()"></div>
    <div class="relative max-w-3xl mx-auto h-full flex items-center justify-center px-4">
        <div class="w-full max-h-[90vh] overflow-y-auto rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-800">
                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Activity details</h3>
                <button type="button" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" onclick="closeActivityMeta()" aria-label="Close details">
                    &times;
                </button>
            </div>
            <div id="activityDetailContent" class="px-5 py-4 space-y-4 text-sm text-gray-700 dark:text-gray-200"></div>
        </div>
    </div>
</div>

@php
    $activityMeta = $logs->count()
        ? $logs->keyBy('id')->map(function($log) use ($displayUserName){
            $meta = $log->meta ?? [];
            if (is_array($meta)) {
                unset($meta['payment_method'], $meta['reference_number'], $meta['credit_applied'], $meta['remaining_credit']);
                if (isset($meta['payment_details']) && is_array($meta['payment_details'])) {
                    unset($meta['payment_details']['credit_applied'], $meta['payment_details']['remaining_credit']);
                    if (empty($meta['payment_details'])) {
                        unset($meta['payment_details']);
                    }
                }
            }
            return [
                'id' => $log->id,
                'module' => $log->module,
                'action' => $log->action,
                'description' => $log->description,
                'user' => $displayUserName($log->user ?? null),
                'timestamp' => $log->created_at->format('Y-m-d H:i:s'),
                'target_type' => $log->target_type ? class_basename($log->target_type) : null,
                'target_id' => $log->target_id,
                'meta' => $meta,
            ];
        })->toArray()
        : [];
@endphp

<script>
    window.__activityMeta = @json($activityMeta);

    function showActivityMeta(id){
        const modal = document.getElementById('activityDetailModal');
        const content = document.getElementById('activityDetailContent');
        if (!modal || !content) {
            alert('Unable to open details right now.');
            return;
        }

        const entry = window.__activityMeta[id];
        if (!entry) {
            content.innerHTML = '<p class="text-gray-500 dark:text-gray-400">No additional details recorded for this action.</p>';
        } else {
            const baseInfo = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Performed by</p>
                        <p class="font-medium text-gray-900 dark:text-gray-100">${entry.user}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">When</p>
                        <p class="font-medium text-gray-900 dark:text-gray-100">${entry.timestamp}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Module</p>
                        <p class="font-medium text-gray-900 dark:text-gray-100">${entry.module}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Action</p>
                        <p class="font-medium text-gray-900 dark:text-gray-100">${entry.action}</p>
                    </div>
                </div>
            `;

            const description = `
                <div class="rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/60 px-4 py-3">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Summary</p>
                    <p class="text-gray-800 dark:text-gray-100">${entry.description}</p>
                </div>
            `;

            let targetBlock = '';
            if (entry.target_type || entry.target_id) {
                const targetItems = [];
                if (entry.target_type) targetItems.push(`<span class="font-medium">Type:</span> ${entry.target_type}`);
                if (entry.target_id) targetItems.push(`<span class="font-medium">ID:</span> ${entry.target_id}`);
                targetBlock = `
                    <div class="rounded-lg border border-gray-200 dark:border-gray-800 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Target</p>
                        <p class="space-x-4 text-gray-800 dark:text-gray-100">${targetItems.join(' | ')}</p>
                    </div>
                `;
            }

            const metaEntries = formatMetaEntries(entry.meta);

            content.innerHTML = `${baseInfo}${description}${targetBlock}${metaEntries}`;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function closeActivityMeta(){
        const modal = document.getElementById('activityDetailModal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }

    const SKIP_META_FIELDS = new Set(['payment_method', 'reference_number', 'remaining_credit', 'credit_applied']);

    function formatMetaEntries(meta) {
        if (!meta || (typeof meta === 'object' && Object.keys(meta).length === 0)) {
            return '<p class="text-xs text-gray-500 dark:text-gray-400">No additional metadata was captured for this action.</p>';
        }

        const renderList = (items) => {
            if (!items.length) {
                return '<span class="italic text-gray-500 dark:text-gray-400">n/a</span>';
            }
            return `<ul class="mt-1 space-y-1 text-gray-800 dark:text-gray-100 text-sm list-disc list-inside">${items.map(item => `<li>${renderValue(item)}</li>`).join('')}</ul>`;
        };

        const renderObject = (obj) => {
            const entries = Object.entries(obj || {})
                .filter(([key]) => !SKIP_META_FIELDS.has(String(key).toLowerCase()));
            if (!entries.length) {
                return '<span class="italic text-gray-500 dark:text-gray-400">n/a</span>';
            }
            return `<dl class="mt-1 space-y-1 text-sm">${entries.map(([k,v]) => {
                const label = k.replace(/[_-]/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
                let valueContent;
                if (Array.isArray(v)) {
                    valueContent = renderList(v);
                } else if (typeof v === 'object' && v !== null) {
                    valueContent = renderObject(v);
                } else {
                    valueContent = `<span class="text-gray-800 dark:text-gray-100">${v ?? '<span class="italic text-gray-500 dark:text-gray-400">n/a</span>'}</span>`;
                }
                return `<div class="flex flex-col">
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">${label}</dt>
                    <dd>${valueContent}</dd>
                </div>`;
            }).join('')}</dl>`;
        };

        const renderValue = (value) => {
            if (value === null || value === undefined || value === '') {
                return '<span class="italic text-gray-500 dark:text-gray-400">n/a</span>';
            }
            if (Array.isArray(value)) {
                return renderList(value);
            }
            if (typeof value === 'object') {
                return renderObject(value);
            }
            return `<span class="text-gray-800 dark:text-gray-100">${value}</span>`;
        };

        if (Array.isArray(meta)) {
            const filtered = meta.filter(item => {
                if (item === null || item === undefined) return false;
                if (typeof item === 'string' && item.trim() === '') return false;
                if (Array.isArray(item) && item.length === 0) return false;
                if (typeof item === 'object' && !Array.isArray(item) && Object.keys(item || {}).length === 0) return false;
                return true;
            });

            if (!filtered.length) {
                return '<p class="text-xs text-gray-500 dark:text-gray-400">No additional metadata was captured for this action.</p>';
            }

            const items = filtered.map((item) => `<li class="border border-gray-200 dark:border-gray-800 rounded-md px-3 py-2">${renderValue(item)}</li>`).join('');
            return `<div><p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Metadata</p><ul class="space-y-2">${items}</ul></div>`;
        }

        const entries = Object.entries(meta)
            .filter(([key, value]) => {
                if (SKIP_META_FIELDS.has(String(key).toLowerCase())) {
                    return false;
                }

                if (value === null || value === undefined) {
                    return false;
                }

                if (typeof value === 'string' && value.trim() === '') {
                    return false;
                }

                if (Array.isArray(value) && value.length === 0) {
                    return false;
                }

                if (typeof value === 'object' && !Array.isArray(value) && Object.keys(value || {}).length === 0) {
                    return false;
                }

                return true;
            })
            .map(([key, value]) => {
            const label = key.replace(/[_-]/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
            return `
                <div class="rounded-lg border border-gray-200 dark:border-gray-800 px-4 py-3">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">${label}</p>
                    ${renderValue(value)}
                </div>
            `;
        }).join('');

        return `<div class="space-y-2"><p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Metadata</p>${entries}</div>`;
    }

    document.addEventListener('DOMContentLoaded', function(){
        var btn = document.getElementById('toggleFilters');
        var panel = document.getElementById('filtersPanel');
        if (btn && panel) {
            btn.addEventListener('click', function(){
                panel.classList.toggle('hidden');
            });
        }

        document.addEventListener('keydown', function(event){
            if (event.key === 'Escape') {
                closeActivityMeta();
            }
        });
    });
</script>
@endsection
