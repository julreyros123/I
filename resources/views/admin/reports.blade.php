@extends('layouts.admin')

@section('title', 'Issue Complaints')

@section('content')
    <div class="max-w-7xl mx-auto px-6 py-8 font-[Poppins] space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-6">
            <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-1">Issue Complaints</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                These are issue complaints submitted by staff through the <span class="font-medium">Report Issue</span> button in the staff portal.
            </p>
            <div class="space-y-6">
                {{-- Priority Issues --}}
                <div>
                    <h2 class="text-sm font-semibold text-red-600 dark:text-red-400 mb-2">Priority</h2>
                    <div class="overflow-x-auto border border-red-100 dark:border-red-900/50 rounded-xl">
                        <table class="min-w-full text-xs text-left text-gray-700 dark:text-gray-200">
                            <thead class="bg-red-50 dark:bg-red-900/40">
                                <tr>
                                    <th class="px-3 py-2">Date</th>
                                    <th class="px-3 py-2">Reporter</th>
                                    <th class="px-3 py-2">Category</th>
                                    <th class="px-3 py-2">Other</th>
                                    <th class="px-3 py-2">Message</th>
                                    <th class="px-3 py-2 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($priorityReports as $r)
                                <tr>
                                    <td class="px-3 py-2 align-top">{{ optional($r->created_at)->format('Y-m-d H:i') }}</td>
                                    <td class="px-3 py-2 align-top">{{ $r->user->name ?? '—' }}</td>
                                    <td class="px-3 py-2 align-top">{{ $r->category ?? '—' }}</td>
                                    <td class="px-3 py-2 align-top">{{ $r->other_problem ?? '—' }}</td>
                                    <td class="px-3 py-2 align-top max-w-md break-words">{{ $r->message }}</td>
                                    <td class="px-3 py-2 align-top text-right space-y-1">
                                        <form method="POST" action="{{ route('admin.reports.priority', $r->id) }}">
                                            @csrf
                                            <input type="hidden" name="is_priority" value="0">
                                            <button type="submit" class="inline-flex items-center px-2 py-1 rounded text-[11px] bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200">Remove priority</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.reports.status', $r->id) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="inline-flex items-center px-2 py-1 rounded text-[11px] bg-green-600 hover:bg-green-700 text-white">Mark completed</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="px-3 py-3 text-center text-gray-400">No priority issues.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Open (non-priority) Issues --}}
                <div>
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-2">Open</h2>
                    <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-xl">
                        <table class="min-w-full text-xs text-left text-gray-700 dark:text-gray-200">
                            <thead class="bg-gray-100 dark:bg-gray-700">
                                <tr>
                                    <th class="px-3 py-2">Date</th>
                                    <th class="px-3 py-2">Reporter</th>
                                    <th class="px-3 py-2">Category</th>
                                    <th class="px-3 py-2">Other</th>
                                    <th class="px-3 py-2">Message</th>
                                    <th class="px-3 py-2 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($openReports as $r)
                                <tr>
                                    <td class="px-3 py-2 align-top">{{ optional($r->created_at)->format('Y-m-d H:i') }}</td>
                                    <td class="px-3 py-2 align-top">{{ $r->user->name ?? '—' }}</td>
                                    <td class="px-3 py-2 align-top">{{ $r->category ?? '—' }}</td>
                                    <td class="px-3 py-2 align-top">{{ $r->other_problem ?? '—' }}</td>
                                    <td class="px-3 py-2 align-top max-w-md break-words">{{ $r->message }}</td>
                                    <td class="px-3 py-2 align-top text-right space-y-1">
                                        <form method="POST" action="{{ route('admin.reports.priority', $r->id) }}">
                                            @csrf
                                            <input type="hidden" name="is_priority" value="1">
                                            <button type="submit" class="inline-flex items-center px-2 py-1 rounded text-[11px] bg-red-600 hover:bg-red-700 text-white">Mark priority</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.reports.status', $r->id) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="inline-flex items-center px-2 py-1 rounded text-[11px] bg-green-600 hover:bg-green-700 text-white">Mark completed</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="px-3 py-3 text-center text-gray-400">No open issues.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Completed Issues --}}
                <div>
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-2">Completed</h2>
                    <div class="overflow-x-auto border border-green-100 dark:border-green-900/40 rounded-xl">
                        <table class="min-w-full text-xs text-left text-gray-700 dark:text-gray-200">
                            <thead class="bg-green-50 dark:bg-green-900/40">
                                <tr>
                                    <th class="px-3 py-2">Date</th>
                                    <th class="px-3 py-2">Reporter</th>
                                    <th class="px-3 py-2">Category</th>
                                    <th class="px-3 py-2">Other</th>
                                    <th class="px-3 py-2">Message</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($completedReports as $r)
                                <tr>
                                    <td class="px-3 py-2 align-top">{{ optional($r->created_at)->format('Y-m-d H:i') }}</td>
                                    <td class="px-3 py-2 align-top">{{ $r->user->name ?? '—' }}</td>
                                    <td class="px-3 py-2 align-top">{{ $r->category ?? '—' }}</td>
                                    <td class="px-3 py-2 align-top">{{ $r->other_problem ?? '—' }}</td>
                                    <td class="px-3 py-2 align-top max-w-md break-words">{{ $r->message }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="px-3 py-3 text-center text-gray-400">No completed issues.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
