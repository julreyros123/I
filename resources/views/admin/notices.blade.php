@extends('layouts.admin')

@section('title', 'Notice to Staff')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-8 font-[Poppins]">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-6">
        <h1 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4">Send Notice to Staff</h1>
        <form id="broadcastForm" class="space-y-4">
            <div>
                <label class="block text-sm mb-1">Title</label>
                <input type="text" id="broadcastTitle" class="w-full border rounded-lg px-3 h-[42px] bg-white dark:bg-gray-700" required>
            </div>
            <div>
                <label class="block text-sm mb-1">Message</label>
                <textarea id="broadcastMsg" rows="4" class="w-full border rounded-lg px-3 py-2 bg-white dark:bg-gray-700" required></textarea>
            </div>
            <div class="text-right">
                <button class="px-4 h-[40px] rounded bg-blue-600 hover:bg-blue-700 text-white">Send</button>
            </div>
        </form>
        <p id="broadcastStatus" class="text-sm mt-2"></p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-6 mt-6">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Recent Notices</h2>
            <button id="refreshRecent" class="text-sm text-blue-600 hover:text-blue-700">Refresh</button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-200">
                <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-2">Date</th>
                        <th class="px-4 py-2">Title</th>
                        <th class="px-4 py-2">Message</th>
                        <th class="px-4 py-2">Audience</th>
                    </tr>
                </thead>
                <tbody id="recentNotices" class="divide-y divide-gray-200 dark:divide-gray-700"></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async function(){
    const form = document.getElementById('broadcastForm');
    const statusEl = document.getElementById('broadcastStatus');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        statusEl.textContent = 'Sending...';
        const res = await fetch("{{ route('api.notifications.broadcast') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content')||'' },
            body: JSON.stringify({ title: document.getElementById('broadcastTitle').value, message: document.getElementById('broadcastMsg').value })
        });
        statusEl.textContent = res.ok ? 'Notice sent.' : 'Failed to send.';
        if (res.ok) { form.reset(); loadRecent(); }
    });

    async function loadRecent(){
        const res = await fetch("{{ route('api.notifications.recent') }}");
        if (!res.ok) return;
        const data = await res.json();
        const rows = data.items || [];
        document.getElementById('recentNotices').innerHTML = rows.length ? rows.map(n => `
            <tr>
                <td class="px-4 py-2">${(new Date(n.created_at)).toLocaleString()}</td>
                <td class="px-4 py-2">${n.title}</td>
                <td class="px-4 py-2">${n.message ?? ''}</td>
                <td class="px-4 py-2">${n.audience}</td>
            </tr>
        `).join('') : '<tr><td colspan="4" class="px-4 py-4 text-center text-gray-500">No notices yet.</td></tr>';
    }
    loadRecent();
    document.getElementById('refreshRecent').addEventListener('click', loadRecent);
});
</script>
@endpush


