@extends('layouts.admin')

@section('title', 'Issue Complaints')

@php use Illuminate\Support\Str; @endphp

@section('content')
    <div class="max-w-7xl mx-auto px-6 py-8 font-[Poppins] space-y-6">
        @php
            $incomingReports = $priorityReports->concat($openReports)->sortByDesc('created_at');
            $newTodayCount = $incomingReports->filter(fn ($report) => optional($report->created_at)->isToday())->count();
            $priorityCount = $priorityReports->count();
            $openCount = $openReports->count();
            $completedCount = $completedReports->count();
        @endphp

        <section class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 shadow-lg p-6">
            <header class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div class="space-y-2">
                    <span class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.35em] text-blue-500 dark:text-blue-300">Staff Requests</span>
                    <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900 dark:text-gray-100">Issue Complaints Command Center</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-2xl">
                        Complaints raised inside the staff workspace land here. Review the live queue, escalate urgent cases, and close the loop with staff.
                    </p>
                </div>
                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full border border-blue-200 dark:border-blue-800 bg-blue-50/70 dark:bg-blue-900/30 text-blue-700 dark:text-blue-200">
                        <span class="inline-block w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                        Live feed active
                    </span>
                </div>
            </header>

            <dl class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div class="rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/70 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-[0.25em] text-gray-500 dark:text-gray-400">New today</dt>
                    <dd class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $newTodayCount }}</dd>
                    <dd class="text-xs text-gray-500 dark:text-gray-400">Complaints received in the last 24 hours.</dd>
                </div>
                <div class="rounded-2xl border border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/30 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-[0.25em] text-amber-600 dark:text-amber-300">Priority</dt>
                    <dd class="mt-2 text-3xl font-semibold text-amber-700 dark:text-amber-100">{{ $priorityCount }}</dd>
                    <dd class="text-xs text-amber-600/80 dark:text-amber-200/80">Escalated issues needing immediate action.</dd>
                </div>
                <div class="rounded-2xl border border-sky-200 dark:border-sky-700 bg-sky-50 dark:bg-sky-900/30 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-[0.25em] text-sky-600 dark:text-sky-300">Open queue</dt>
                    <dd class="mt-2 text-3xl font-semibold text-sky-700 dark:text-sky-100">{{ $openCount }}</dd>
                    <dd class="text-xs text-sky-600/80 dark:text-sky-200/80">Waiting triage or follow-up.</dd>
                </div>
                <div class="rounded-2xl border border-emerald-200 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-900/30 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-[0.25em] text-emerald-600 dark:text-emerald-300">Resolved</dt>
                    <dd class="mt-2 text-3xl font-semibold text-emerald-700 dark:text-emerald-100">{{ $completedCount }}</dd>
                    <dd class="text-xs text-emerald-600/80 dark:text-emerald-200/80">Marked completed and communicated back.</dd>
                </div>
            </dl>

            <div x-data="{ tab: 'incoming' }" class="mt-8">
                <div class="flex flex-wrap items-center gap-2 border-b border-gray-200 dark:border-gray-800 pb-2">
                    <button @click="tab = 'incoming'" :class="tab === 'incoming' ? 'bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'" class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition">
                        <span class="inline-flex h-2.5 w-2.5 rounded-full" :class="tab === 'incoming' ? 'bg-emerald-300' : 'bg-gray-400 dark:bg-gray-500'"></span>
                        Incoming queue
                        <span class="inline-flex items-center justify-center text-[11px] rounded-full px-2 py-0.5 bg-white/40 dark:bg-black/20">{{ $incomingReports->count() }}</span>
                    </button>
                    <button @click="tab = 'priority'" :class="tab === 'priority' ? 'bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'" class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition">
                        <x-heroicon-o-fire class="w-4 h-4" /> Priority lane
                        <span class="inline-flex items-center justify-center text-[11px] rounded-full px-2 py-0.5 bg-white/40 dark:bg-black/20">{{ $priorityCount }}</span>
                    </button>
                    <button @click="tab = 'completed'" :class="tab === 'completed' ? 'bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'" class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition">
                        <x-heroicon-o-check-circle class="w-4 h-4" /> Completed
                        <span class="inline-flex items-center justify-center text-[11px] rounded-full px-2 py-0.5 bg-white/40 dark:bg-black/20">{{ $completedCount }}</span>
                    </button>
                </div>

                <div x-show="tab === 'incoming'" x-cloak class="mt-6 space-y-4">
                    @forelse($incomingReports as $report)
                        <article class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div class="space-y-1">
                                    <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.3em]">
                                        <span class="px-2 py-1 rounded-full {{ $report->is_priority ? 'bg-red-50 text-red-600 border border-red-200 dark:bg-red-900/30 dark:text-red-200 dark:border-red-700' : 'bg-gray-100 text-gray-500 border border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700' }}">{{ $report->is_priority ? 'Priority' : 'Standard' }}</span>
                                        @if($report->category)
                                            <span class="px-2 py-1 rounded-full bg-blue-50 text-blue-600 border border-blue-200 dark:bg-blue-900/30 dark:text-blue-200 dark:border-blue-700">{{ $report->category }}</span>
                                        @endif
                                        <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-500 border border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700">Staff request</span>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ Str::limit($report->message, 140) }}</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 max-w-2xl">{{ $report->message }}</p>
                                </div>
                                <div class="text-right text-xs text-gray-500 dark:text-gray-400 space-y-1">
                                    <span class="block font-semibold text-gray-700 dark:text-gray-200">{{ optional($report->created_at)->format('M d, Y • h:i A') }}</span>
                                    <span>{{ optional($report->created_at)->diffForHumans() }}</span>
                                </div>
                            </div>

                            <dl class="mt-4 grid gap-3 text-sm text-gray-600 dark:text-gray-300 sm:grid-cols-3">
                                <div>
                                    <dt class="text-xs uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Reporter</dt>
                                    <dd class="mt-1 font-medium">{{ $report->user->name ?? 'Unknown Staff' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Contact</dt>
                                    <dd class="mt-1">{{ $report->user->email ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Other notes</dt>
                                    <dd class="mt-1">{{ $report->other_problem ?: '—' }}</dd>
                                </div>
                            </dl>

                            <footer class="mt-5 flex flex-wrap items-center gap-2 justify-between">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Ticket ID: {{ $report->id }}</span>
                                <div class="flex flex-wrap items-center gap-2">
                                    @if(!$report->is_priority)
                                        <form method="POST" action="{{ route('admin.reports.priority', $report->id) }}">
                                            @csrf
                                            <input type="hidden" name="is_priority" value="1">
                                            <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 text-sm font-semibold transition">
                                                <x-heroicon-o-fire class="w-4 h-4" /> Escalate
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.reports.priority', $report->id) }}">
                                            @csrf
                                            <input type="hidden" name="is_priority" value="0">
                                            <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-100 px-4 py-2 text-sm font-semibold transition">
                                                <x-heroicon-o-arrow-uturn-left class="w-4 h-4" /> Remove priority
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.reports.status', $report->id) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 text-sm font-semibold transition">
                                            <x-heroicon-o-check class="w-4 h-4" /> Mark completed
                                        </button>
                                    </form>
                                </div>
                            </footer>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-10 text-center">
                            <x-heroicon-o-sparkles class="mx-auto w-10 h-10 text-gray-400 dark:text-gray-500" />
                            <p class="mt-3 text-sm font-medium text-gray-600 dark:text-gray-300">All clear! No active complaints from staff.</p>
                        </div>
                    @endforelse
                </div>

                <div x-show="tab === 'priority'" x-cloak class="mt-6 space-y-4">
                    @forelse($priorityReports as $report)
                        <article class="rounded-2xl border border-red-200 dark:border-red-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
                            <header class="flex flex-wrap items-start justify-between gap-4">
                                <div class="space-y-1">
                                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-50 border border-red-200 text-xs font-semibold uppercase tracking-[0.25em] text-red-500 dark:bg-red-900/30 dark:border-red-800 dark:text-red-200">
                                        <x-heroicon-o-fire class="w-4 h-4" /> Priority request
                                    </div>
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ Str::limit($report->message, 160) }}</h2>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 max-w-2xl">{{ $report->message }}</p>
                                </div>
                                <div class="text-right text-xs text-gray-500 dark:text-gray-400 space-y-1">
                                    <span class="font-semibold text-gray-700 dark:text-gray-200">{{ optional($report->created_at)->format('M d, Y • h:i A') }}</span>
                                    <span>{{ optional($report->created_at)->diffForHumans() }}</span>
                                </div>
                            </header>

                            <dl class="mt-4 grid gap-3 text-sm text-gray-600 dark:text-gray-300 sm:grid-cols-3">
                                <div>
                                    <dt class="text-xs uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Reporter</dt>
                                    <dd class="mt-1 font-medium">{{ $report->user->name ?? 'Unknown Staff' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Contact</dt>
                                    <dd class="mt-1">{{ $report->user->email ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Other notes</dt>
                                    <dd class="mt-1">{{ $report->other_problem ?: '—' }}</dd>
                                </div>
                            </dl>

                            <footer class="mt-5 flex flex-wrap items-center gap-2 justify-between">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Ticket ID: {{ $report->id }}</span>
                                <div class="flex flex-wrap items-center gap-2">
                                    <form method="POST" action="{{ route('admin.reports.priority', $report->id) }}">
                                        @csrf
                                        <input type="hidden" name="is_priority" value="0">
                                        <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-100 px-4 py-2 text-sm font-semibold transition">
                                            <x-heroicon-o-arrow-uturn-left class="w-4 h-4" /> Remove priority
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.reports.status', $report->id) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 text-sm font-semibold transition">
                                            <x-heroicon-o-check class="w-4 h-4" /> Mark completed
                                        </button>
                                    </form>
                                </div>
                            </footer>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-red-300 dark:border-red-700 bg-red-50/60 dark:bg-red-900/20 p-10 text-center text-red-600 dark:text-red-200">
                            <x-heroicon-o-hand-raised class="mx-auto w-10 h-10" />
                            <p class="mt-3 text-sm font-medium">No complaints have been escalated. Great job staying ahead!</p>
                        </div>
                    @endforelse
                </div>

                <div x-show="tab === 'completed'" x-cloak class="mt-6 space-y-4">
                    @forelse($completedReports as $report)
                        <article class="rounded-2xl border border-emerald-200 dark:border-emerald-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
                            <header class="flex flex-wrap items-start justify-between gap-4">
                                <div class="space-y-1">
                                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-xs font-semibold uppercase tracking-[0.25em] text-emerald-600 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-200">
                                        <x-heroicon-o-check-badge class="w-4 h-4" /> Completed
                                    </span>
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ Str::limit($report->message, 160) }}</h2>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 max-w-2xl">{{ $report->message }}</p>
                                </div>
                                <div class="text-right text-xs text-gray-500 dark:text-gray-400 space-y-1">
                                    <span class="font-semibold text-gray-700 dark:text-gray-200">{{ optional($report->created_at)->format('M d, Y • h:i A') }}</span>
                                    <span>completed {{ optional($report->updated_at)->diffForHumans() }}</span>
                                </div>
                            </header>

                            <dl class="mt-4 grid gap-3 text-sm text-gray-600 dark:text-gray-300 sm:grid-cols-3">
                                <div>
                                    <dt class="text-xs uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Reporter</dt>
                                    <dd class="mt-1 font-medium">{{ $report->user->name ?? 'Unknown Staff' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Follow-up note</dt>
                                    <dd class="mt-1">{{ $report->other_problem ?: '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Reference ID</dt>
                                    <dd class="mt-1">#{{ $report->id }}</dd>
                                </div>
                            </dl>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-emerald-300 dark:border-emerald-700 bg-emerald-50/60 dark:bg-emerald-900/20 p-10 text-center text-emerald-700 dark:text-emerald-200">
                            <x-heroicon-o-check-circle class="mx-auto w-10 h-10" />
                            <p class="mt-3 text-sm font-medium">No completed complaints logged yet. Close tickets from the queue to build this record.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
@endsection
