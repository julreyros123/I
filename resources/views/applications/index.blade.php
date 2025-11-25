@extends('layouts.app')

@section('title', 'Applications')

@section('content')
<div class="max-w-7xl mx-auto p-8 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Applications</h1>
        <form method="GET" class="flex items-center gap-2 text-sm">
            <select name="status" class="border rounded px-2 h-[36px] bg-white dark:bg-gray-700 dark:text-gray-100 border-gray-300 dark:border-gray-600">
                <option value="">All Status</option>
                @foreach(['registered','inspected','approved','assessed','paid','scheduled','installed','rejected'] as $st)
                    <option value="{{ $st }}" @selected(request('status')===$st)>{{ ucfirst($st) }}</option>
                @endforeach
            </select>
            <select name="decision" class="border rounded px-2 h-[36px] bg-white dark:bg-gray-700 dark:text-gray-100 border-gray-300 dark:border-gray-600">
                <option value="">All Decisions</option>
                @foreach(['approve','review','reject'] as $dc)
                    <option value="{{ $dc }}" @selected(request('decision')===$dc)>{{ ucfirst($dc) }}</option>
                @endforeach
            </select>
            <select name="risk" class="border rounded px-2 h-[36px] bg-white dark:bg-gray-700 dark:text-gray-100 border-gray-300 dark:border-gray-600">
                <option value="">All Risk</option>
                @foreach(['low','medium','high'] as $rk)
                    <option value="{{ $rk }}" @selected(request('risk')===$rk)>{{ ucfirst($rk) }}</option>
                @endforeach
            </select>
            <button class="px-3 h-[36px] rounded bg-blue-600 text-white">Filter</button>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-gray-600 dark:text-gray-300">
                    <tr>
                        <th class="py-2 pr-3">Applicant</th>
                        <th class="py-2 pr-3">Address</th>
                        <th class="py-2 pr-3">Score</th>
                        <th class="py-2 pr-3">Risk</th>
                        <th class="py-2 pr-3">Status</th>
                        <th class="py-2 pr-3">Decision</th>
                        <th class="py-2 pr-3">Submitted</th>
                        <th class="py-2 pr-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($applications as $app)
                        <tr>
                            <td class="py-3 pr-3">
                                <a href="{{ route('applications.show', $app->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ $app->applicant_name }}</a>
                            </td>
                            <td class="py-3 pr-3 text-gray-600 dark:text-gray-300">{{ $app->address }}</td>
                            <td class="py-3 pr-3">
                                @if(!is_null($app->score))
                                    <span class="px-2 py-0.5 rounded text-xs {{ $app->score>=80 ? 'bg-green-100 text-green-700' : ($app->score>=60 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">{{ $app->score }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="py-3 pr-3">
                                @if($app->risk_level)
                                    <span class="px-2 py-0.5 rounded text-xs {{ $app->risk_level==='low' ? 'bg-green-100 text-green-700' : ($app->risk_level==='medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">{{ ucfirst($app->risk_level) }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="py-3 pr-3">{{ ucfirst($app->status) }}</td>
                            <td class="py-3 pr-3">{{ $app->decision ? ucfirst($app->decision) : '—' }}</td>
                            <td class="py-3 pr-3 text-gray-500">{{ $app->created_at->format('Y-m-d H:i') }}</td>
                            <td class="py-3 pr-3">
                                <div class="flex items-center gap-2">
                                    <button class="px-2 py-1 rounded border text-xs rescore-btn" data-id="{{ $app->id }}">Re-score</button>
                                    <button class="px-2 py-1 rounded bg-green-600 text-white text-xs approve-btn" data-id="{{ $app->id }}">Approve</button>
                                    <button class="px-2 py-1 rounded bg-red-600 text-white text-xs reject-btn" data-id="{{ $app->id }}">Reject</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-6 text-center text-gray-500">No applications found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function(){
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    document.addEventListener('click', async (e) => {
        const rescore = e.target.closest?.('.rescore-btn');
        const approve = e.target.closest?.('.approve-btn');
        const reject = e.target.closest?.('.reject-btn');
        try{
            if (rescore){
                const id = rescore.getAttribute('data-id');
                const r = await fetch(`/api/applications/${id}/score`, { method:'POST', headers:{'Accept':'application/json','X-CSRF-TOKEN': token} });
                if (!r.ok) throw 0; location.reload(); return;
            }
            if (approve){
                const id = approve.getAttribute('data-id');
                const r = await fetch(`/api/applications/${id}/approve`, { method:'PUT', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN': token}, body: JSON.stringify({ auto_verify: false }) });
                if (!r.ok) throw 0; location.reload(); return;
            }
            if (reject){
                const id = reject.getAttribute('data-id');
                const reason = prompt('Reason (optional)');
                const r = await fetch(`/api/applications/${id}/reject`, { method:'PUT', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN': token}, body: JSON.stringify({ reason }) });
                if (!r.ok) throw 0; location.reload(); return;
            }
        }catch(_){ alert('Action failed'); }
    });
})();
</script>
@endsection
