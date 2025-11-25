@extends('layouts.admin')

@section('title', 'Employee Management')

@section('content')
<div class="w-full mx-auto px-4 sm:px-6 py-4 sm:py-6 lg:py-8 font-[Poppins] space-y-6 lg:space-y-8">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Employee Management</h1>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.employees.add') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 text-sm font-medium">
                <x-heroicon-o-plus class="w-4 h-4" />
                Add Employee Record
            </a>
        </div>
    </div>

    <!-- Employee Statistics -->
    <div class="grid grid-cols-12 gap-6 mt-4 md:mt-6">
        <div class="col-span-12 grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-4 lg:p-5 flex flex-col justify-center">
                <p class="text-xs text-gray-500 dark:text-gray-400">Total Employees</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ number_format($stats['total_employees'] ?? 0) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-4 lg:p-5 flex flex-col justify-center">
                <p class="text-xs text-gray-500 dark:text-gray-400">Active Staff</p>
                <p class="text-2xl font-bold text-green-600">{{ number_format($stats['active_employees'] ?? 0) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-4 lg:p-5 flex flex-col justify-center">
                <p class="text-xs text-gray-500 dark:text-gray-400">On Leave</p>
                <p class="text-2xl font-bold text-yellow-600">{{ number_format($stats['on_leave'] ?? 0) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 p-4 lg:p-5 flex flex-col justify-center">
                <p class="text-xs text-gray-500 dark:text-gray-400">Departments</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ number_format($stats['departments'] ?? 0) }}</p>
            </div>
        </div>
    </div>

    <!-- Employee Table -->
    <div class="grid grid-cols-12 gap-4 lg:gap-6">
        <div class="col-span-12 bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-3 lg:p-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <form method="GET" class="w-full">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3 w-full">
                        <div class="flex w-full sm:w-auto justify-start sm:justify-end gap-3 sm:ml-auto">
                            <div class="w-full sm:w-32">
                                <label for="dept" class="sr-only">Department</label>
                                <select id="dept" name="dept" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                                    <option value="">All Departments</option>
                                    <option value="billing">Billing</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="customer-service">Customer Service</option>
                                    <option value="administration">Administration</option>
                                </select>
                            </div>
                            <div class="relative w-full sm:w-40">
                                <label for="q" class="sr-only">Search employees</label>
                                <input id="q" name="q" type="text" placeholder="Search"
                                       class="w-full pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                                <x-heroicon-o-magnifying-glass aria-hidden="true" class="pointer-events-none w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500" />
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm js-datatable">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-2 text-left">Employee</th>
                        <th class="px-4 py-2 text-left">ID Number</th>
                        <th class="px-4 py-2 text-left">Department</th>
                        <th class="px-4 py-2 text-left">Position</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left">Hire Date</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-100">
                    <tr>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0">
                                    <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=John+Doe&background=3B82F6&color=fff" alt="">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">John Doe</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">john.doe@mawasa.com</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">EMP-001</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">Administration</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">System Administrator</td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                Active
                            </span>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">Jan 15, 2023</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-right">
                            <button class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3">Edit</button>
                            <button class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0">
                                    <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=Jane+Smith&background=EC4899&color=fff" alt="">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Jane Smith</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">jane.smith@mawasa.com</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">EMP-002</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">Billing</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">Billing Specialist</td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                Active
                            </span>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">Mar 10, 2023</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-right">
                            <button class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3">Edit</button>
                            <button class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0">
                                    <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=Mike+Johnson&background=F59E0B&color=fff" alt="">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Mike Johnson</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">mike.johnson@mawasa.com</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">EMP-003</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">Maintenance</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">Maintenance Technician</td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                On Leave
                            </span>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">Jun 20, 2023</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-right">
                            <button class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3">Edit</button>
                            <button class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div class="text-sm text-gray-700 dark:text-gray-300">
                Showing <span class="font-medium">1</span> to <span class="font-medium">3</span> of <span class="font-medium">3</span> results
            </div>
            <div class="flex gap-2">
                <button class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50" disabled>
                    Previous
                </button>
                <button class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50" disabled>
                    Next
                </button>
            </div>
        </div>
    </div>
    </div>
    </div>
</div>
@endsection
