@extends('layouts.admin')

@section('title', 'Issue Complaints')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8 font-[Poppins]">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-6">
        <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4">Issue Complaints</h1>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-200">
                <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-2">Date</th>
                        <th class="px-4 py-2">Reporter</th>
                        <th class="px-4 py-2">Category</th>
                        <th class="px-4 py-2">Other</th>
                        <th class="px-4 py-2">Message</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($reports as $r)
                    <tr>
                        <td class="px-4 py-2">{{ optional($r->created_at)->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-2">{{ $r->user->name ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $r->category ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $r->other_problem ?? '—' }}</td>
                        <td class="px-4 py-2 max-w-xl break-words">{{ $r->message }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">No issues reported.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
    </div>
</div>
@endsection



