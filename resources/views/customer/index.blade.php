@extends('layouts.app')

@section('title', 'Customers')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8 font-[Inter]">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-extrabold text-gray-800 dark:text-gray-100">
            MAWASA WATER AND SANITATION
        </h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm">
            Customer information and billing computation
        </p>
    </div>

    <!-- Customer Section -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md p-6 transition-all">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Customers</h2>

            <!-- Clear Selected Button -->
            <button id="clearSelectedBtn"
                class="hidden px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium shadow-sm">
                Clear Selected
            </button>
        </div>

        <!-- Search -->
        <div class="mb-6">
            <input type="text" id="searchCustomer"
                class="w-full md:w-1/2 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm 
                       focus:ring-2 focus:ring-blue-500 focus:outline-none bg-gray-50 dark:bg-gray-700 
                       text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-300"
                placeholder="Search by name, address, or account no.">
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-lg">
                <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-semibold">
                    <tr>
                        <th class="px-4 py-3 border-b border-gray-300 dark:border-gray-600">
                            <input type="checkbox" id="selectAll" class="w-4 h-4 accent-blue-600">
                        </th>
                        <th class="px-6 py-3 border-b border-gray-300 dark:border-gray-600">Account No.</th>
                        <th class="px-6 py-3 border-b border-gray-300 dark:border-gray-600">Name</th>
                        <th class="px-6 py-3 border-b border-gray-300 dark:border-gray-600">Address</th>
                        <th class="px-6 py-3 border-b border-gray-300 dark:border-gray-600">Contact No.</th>
                        <th class="px-6 py-3 border-b border-gray-300 dark:border-gray-600">Status</th>
                        <th class="px-6 py-3 border-b border-gray-300 dark:border-gray-600">Created At</th>
                    </tr>
                </thead>
                <tbody id="customerTable" class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($customers as $c)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="px-4 py-3"><input type="checkbox" class="rowCheckbox w-4 h-4 accent-blue-600" value="{{ $c->id }}"></td>
                        <td class="px-6 py-3">{{ $c->account_no }}</td>
                        <td class="px-6 py-3">{{ $c->name }}</td>
                        <td class="px-6 py-3">{{ $c->address }}</td>
                        <td class="px-6 py-3">{{ $c->contact_no ?? '' }}</td>
                        <td class="px-6 py-3 font-medium {{ ($c->status === 'Active') ? 'text-green-600' : (($c->status === 'Disconnected') ? 'text-red-500' : 'text-yellow-500') }}">{{ $c->status }}</td>
                        <td class="px-6 py-3">{{ optional($c->created_at)->format('Y-m-d') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No customers yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $customers->links() }}</div>
    </div>
</div>

<!-- Script Section -->
<script>
    const searchInput = document.getElementById('searchCustomer');
    const selectAll = document.getElementById('selectAll');
    const clearSelectedBtn = document.getElementById('clearSelectedBtn');

    // 🔍 Search Filter
    searchInput.addEventListener('input', function () {
        const search = this.value.toLowerCase();
    document.querySelectorAll('#customerTable tr').forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(search) ? '' : 'none';
        });
    });

    // ✅ Select All Checkbox
    selectAll.addEventListener('change', function () {
        document.querySelectorAll('.rowCheckbox').forEach(cb => cb.checked = this.checked);
        toggleClearButton();
    });

    // 🎯 Individual Checkbox Change
    document.querySelectorAll('.rowCheckbox').forEach(cb => cb.addEventListener('change', toggleClearButton));

    // 🗑️ Clear Selected Rows (Delete from database)
    clearSelectedBtn.addEventListener('click', async () => {
        const checkedBoxes = document.querySelectorAll('.rowCheckbox:checked');
        if (checkedBoxes.length === 0) return;

        // Confirm deletion
        if (!confirm(`Are you sure you want to delete ${checkedBoxes.length} customer(s)? This action cannot be undone.`)) {
            return;
        }

        // Get customer IDs
        const customerIds = Array.from(checkedBoxes).map(cb => cb.value);
        
        try {
            // Show loading state
            clearSelectedBtn.disabled = true;
            clearSelectedBtn.textContent = 'Deleting...';

            // Send delete request
            const response = await fetch('{{ route("customer.deleteMultiple") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    customer_ids: customerIds
                })
            });

            const result = await response.json();

            if (result.ok) {
                // Remove rows from table
                checkedBoxes.forEach(cb => cb.closest('tr').remove());
                selectAll.checked = false;
                toggleClearButton();
                
                // Show success message
                alert(result.message);
                
                // Reload page to refresh data
                window.location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Delete error:', error);
            alert('Failed to delete customers. Please try again.');
        } finally {
            // Reset button state
            clearSelectedBtn.disabled = false;
            clearSelectedBtn.textContent = 'Clear Selected';
        }
    });

    function toggleClearButton() {
        const anyChecked = document.querySelectorAll('.rowCheckbox:checked').length > 0;
        clearSelectedBtn.classList.toggle('hidden', !anyChecked);
    }
</script>
@endsection

