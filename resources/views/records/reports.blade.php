@extends('layouts.app')

@section('title', 'Records - Reports')

@section('content')
    <div class="max-w-md mx-auto p-6 text-sm">
        <h1 class="text-lg font-semibold text-blue-700 mb-3">Submit a Report</h1>

        @if (session('success'))
            <div class="mb-4 p-3 rounded bg-green-100 text-green-800 border border-green-300 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('reports.store') }}" class="space-y-3">
            @csrf
            <div>
                <span class="block text-xs font-medium text-gray-700 mb-1">Problem category</span>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="radio" name="category" value="UI bug" {{ old('category','')==='UI bug' ? 'checked' : '' }} class="text-blue-600">
                        <span>UI bug</span>
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="radio" name="category" value="Delay issue" {{ old('category','')==='Delay issue' ? 'checked' : '' }} class="text-blue-600">
                        <span>Delay issue</span>
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="radio" name="category" value="Billing problem" {{ old('category','')==='Billing problem' ? 'checked' : '' }} class="text-blue-600">
                        <span>Billing problem</span>
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="radio" name="category" value="Other" {{ old('category','')==='Other' ? 'checked' : '' }} class="text-blue-600" id="categoryOther">
                        <span>Other</span>
                    </label>
                </div>
                @error('category')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="message" class="block text-xs font-medium text-gray-700 mb-1">Describe the issue</label>
                <textarea id="message" name="message" rows="5" class="w-full border rounded p-2 text-sm {{ $errors->has('message') ? 'border-red-500' : 'border-gray-300' }}" placeholder="Provide as much detail as possible...">{{ old('message') }}</textarea>
                @error('message')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div id="otherWrapper" style="display: none;">
                <label for="other_problem" class="block text-xs font-medium text-gray-700 mb-1">Other problem</label>
                <input type="text" id="other_problem" name="other_problem" value="{{ old('other_problem') }}" class="w-full border rounded p-2 text-sm {{ $errors->has('other_problem') ? 'border-red-500' : 'border-gray-300' }}" placeholder="Specify your problem">
                @error('other_problem')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">Submit Report</button>
        </form>
    </div>
    <script>
        (function() {
            const wrapper = document.getElementById('otherWrapper');
            function toggleOther() {
                const selected = document.querySelector('input[name="category"]:checked');
                if (!wrapper) return;
                wrapper.style.display = selected && selected.value === 'Other' ? 'block' : 'none';
            }
            document.addEventListener('change', function(e){
                if (e.target && e.target.name === 'category') {
                    toggleOther();
                }
            });
            toggleOther();
        })();
    </script>
@endsection