@extends('layouts.app')

@section('title', 'Application Details')

@section('content')
<div class="max-w-6xl mx-auto p-8 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Application #{{ $app->id }}</h1>
        <a href="{{ route('applications.index') }}" class="text-sm text-blue-600 dark:text-blue-400">← Back to Applications</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                <h2 class="font-semibold mb-4 text-gray-800 dark:text-gray-100">Applicant</h2>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div><dt class="text-gray-500">Name</dt><dd class="text-gray-800 dark:text-gray-100">{{ $app->applicant_name }}</dd></div>
                    <div><dt class="text-gray-500">Contact</dt><dd class="text-gray-800 dark:text-gray-100">{{ $app->contact_no ?? '—' }}</dd></div>
                    <div class="md:col-span-2"><dt class="text-gray-500">Address</dt><dd class="text-gray-800 dark:text-gray-100">{{ $app->address }}</dd></div>
                    <div><dt class="text-gray-500">Status</dt><dd>{{ ucfirst($app->status) }}</dd></div>
                    <div><dt class="text-gray-500">Decision</dt><dd>{{ $app->decision ? ucfirst($app->decision) : '—' }}</dd></div>
                    <div><dt class="text-gray-500">Submitted</dt><dd>{{ $app->created_at->format('Y-m-d H:i') }}</dd></div>
                </dl>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                <h2 class="font-semibold mb-4 text-gray-800 dark:text-gray-100">KYC Documents</h2>
                @php($docs = $app->documents ?? [])
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">ID Front</div>
                        @if(!empty($docs['id_front']))
                            <a href="{{ asset('storage/'.$docs['id_front']) }}" target="_blank"><img src="{{ asset('storage/'.$docs['id_front']) }}" class="w-full h-40 object-cover rounded border"/></a>
                        @else
                            <div class="text-gray-400 text-sm">No file</div>
                        @endif
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">ID Back</div>
                        @if(!empty($docs['id_back']))
                            <a href="{{ asset('storage/'.$docs['id_back']) }}" target="_blank"><img src="{{ asset('storage/'.$docs['id_back']) }}" class="w-full h-40 object-cover rounded border"/></a>
                        @else
                            <div class="text-gray-400 text-sm">No file</div>
                        @endif
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">Selfie</div>
                        @if(!empty($docs['selfie']))
                            <a href="{{ asset('storage/'.$docs['selfie']) }}" target="_blank"><img src="{{ asset('storage/'.$docs['selfie']) }}" class="w-full h-40 object-cover rounded border"/></a>
                        @else
                            <div class="text-gray-400 text-sm">No file</div>
                        @endif
                    </div>
                </div>
                <div class="mt-3 text-xs text-gray-600 dark:text-gray-300">ID Type: {{ $docs['id_type'] ?? '—' }} | ID No: {{ $docs['id_number'] ?? '—' }}</div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                <h2 class="font-semibold mb-4 text-gray-800 dark:text-gray-100">Scoring</h2>
                <div class="flex items-center gap-3 mb-2">
                    <div class="text-2xl font-bold {{ $app->score>=80 ? 'text-green-600' : ($app->score>=60 ? 'text-yellow-600' : 'text-red-600') }}">{{ $app->score ?? '—' }}</div>
                    @if($app->risk_level)
                        <span class="px-2 py-0.5 rounded text-xs {{ $app->risk_level==='low' ? 'bg-green-100 text-green-700' : ($app->risk_level==='medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">{{ ucfirst($app->risk_level) }} Risk</span>
                    @endif
                </div>
                <div class="text-xs text-gray-500 mb-3">Decision: {{ $app->decision ? ucfirst($app->decision) : '—' }}</div>
                @php($bd = $app->score_breakdown ?? [])
                <ul class="text-sm space-y-1 text-gray-700 dark:text-gray-200">
                    <li>ID validity: {{ $bd['id_validity'] ?? 0 }}/30</li>
                    <li>Duplicate risk: {{ $bd['duplicate_risk'] ?? 0 }}/25</li>
                    <li>Data completeness: {{ $bd['data_completeness'] ?? 0 }}/20</li>
                    <li>Address quality: {{ $bd['address_quality'] ?? 0 }}/15</li>
                    <li>Manual risk: {{ $bd['manual_risk'] ?? 0 }}/10</li>
                </ul>
                <div class="mt-4 flex items-center gap-2">
                    <button class="px-3 py-2 rounded border text-xs rescore-btn" data-id="{{ $app->id }}">Re-score</button>
                    <button class="px-3 py-2 rounded bg-green-600 text-white text-xs approve-btn" data-id="{{ $app->id }}">Approve</button>
                    <button class="px-3 py-2 rounded bg-red-600 text-white text-xs reject-btn" data-id="{{ $app->id }}">Reject</button>
                </div>
                @if($app->customer_id)
                    <div class="mt-3">
                        <button class="px-3 py-2 rounded bg-blue-600 text-white text-xs verify-btn" data-cid="{{ $app->customer_id }}">Verify Customer</button>
                    </div>
                @endif
            </div>
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
        const verify = e.target.closest?.('.verify-btn');
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
            if (verify){
                const cid = verify.getAttribute('data-cid');
                const r = await fetch(`/api/customer/${cid}/verify`, { method:'PUT', headers:{'Accept':'application/json','X-CSRF-TOKEN': token} });
                if (!r.ok) throw 0; alert('Verified'); return;
            }
        }catch(_){ alert('Action failed'); }
    });
})();
</script>
@endsection
