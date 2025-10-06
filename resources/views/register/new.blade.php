@extends('layouts.app')

@section('title', 'New Customer')

@section('content')
<div class="max-w-md mx-auto p-6 bg-white dark:bg-gray-900 rounded shadow">
    <h1 class="text-2xl font-bold text-green-700 dark:text-green-400 mb-4">New Customer Registration</h1>
    <div class="mb-4">
        <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Account Number</label>
        <div class="flex gap-2">
            <input id="area_code" type="text" value="{{ env('ACCOUNT_AREA_CODE', '12') }}" class="w-16 border rounded px-2 h-[40px]" title="Area">
            <input id="route_code" type="text" value="{{ env('ACCOUNT_ROUTE_CODE', '01') }}" class="w-16 border rounded px-2 h-[40px]" title="Route">
            <input id="account_no" type="text" class="flex-1 border rounded px-3 h-[40px] bg-gray-100" readonly>
            <button id="refreshAcc" type="button" class="px-3 rounded bg-blue-600 text-white text-sm">Generate</button>
        </div>
    </div>

    <script>
        async function fetchAcc(){
            const area = document.getElementById('area_code').value;
            const route = document.getElementById('route_code').value;
            const res = await fetch(`{{ route('customer.nextAccount') }}?area=${encodeURIComponent(area)}&route=${encodeURIComponent(route)}`);
            const data = await res.json();
            document.getElementById('account_no').value = data.account_no || '';
        }
        document.getElementById('refreshAcc').addEventListener('click', fetchAcc);
        document.addEventListener('DOMContentLoaded', fetchAcc);
    </script>

    <form class="grid grid-cols-1 md:grid-cols-2 gap-6" onsubmit="submitNewCustomer(event)">
        <div class="col-span-2">
            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Full Name</label>
            <input type="text" id="full_name"
                   class="w-full border rounded-xl px-4 h-[45px] text-sm shadow-sm
                          bg-white dark:bg-gray-700
                          text-gray-800 dark:text-gray-100
                          border-gray-300 dark:border-gray-600
                          focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>

        <div class="col-span-2">
            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Address</label>
            <textarea rows="3" id="address"
                      class="w-full border rounded-xl px-4 py-2 text-sm shadow-sm
                             bg-white dark:bg-gray-700
                             text-gray-800 dark:text-gray-100
                             border-gray-300 dark:border-gray-600
                             focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Contact Number</label>
            <input type="text" id="contact_no"
                   class="w-full border rounded-xl px-4 h-[45px] text-sm shadow-sm
                          bg-white dark:bg-gray-700
                          text-gray-800 dark:text-gray-100
                          border-gray-300 dark:border-gray-600
                          focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Start Date</label>
            <input type="date"
                   class="w-full border rounded-xl px-4 h-[45px] text-sm shadow-sm
                          bg-white dark:bg-gray-700
                          text-gray-800 dark:text-gray-100
                          border-gray-300 dark:border-gray-600
                          focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Meter No.</label>
            <input type="text" id="new_meter_no"
                   class="w-full border rounded-xl px-4 h-[45px] text-sm shadow-sm
                          bg-white dark:bg-gray-700
                          text-gray-800 dark:text-gray-100
                          border-gray-300 dark:border-gray-600
                          focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Meter Size</label>
            <select id="new_meter_size" class="w-full border rounded-xl px-4 h-[45px] text-sm shadow-sm
                           bg-white dark:bg-gray-700
                           text-gray-800 dark:text-gray-100
                           border-gray-300 dark:border-gray-600
                           focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Select Size</option>
                <option>1/2"</option>
                <option>3/4"</option>
                <option>1"</option>
                <option>2"</option>
            </select>
        </div>

        <div class="col-span-2">
            <button class="w-full bg-blue-600 text-white h-[50px] rounded-xl text-sm font-medium 
                           hover:bg-blue-700 shadow-md transition flex items-center justify-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M18 9v6m3-3h-6m-2-4a4 4 0 11-8 0 4 4 0 018 0zm-6 8a6 6 0 0112 0H7z" />
                </svg>
                <span>Register Account</span>
            </button>
        </div>
    </form>