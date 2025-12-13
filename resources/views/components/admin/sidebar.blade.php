{{-- Admin Sidebar --}}
<div 
    x-data="{ open: false }"
    class="w-64 bg-gradient-to-b from-[#00A2FE] to-[#0085DC] 
           text-white dark:from-gray-950 dark:to-gray-900 
           dark:text-gray-200 min-h-screen flex flex-col 
           fixed left-0 top-0 z-20 font-inter transform 
           transition-transform duration-300 ease-in-out 
           -translate-x-full md:translate-x-0"
    id="sidebar"
    x-cloak
    aria-label="Sidebar Navigation"
>
    <div class="h-[65px] px-6 border-b border-[#0085DC] dark:border-gray-800 flex items-center gap-3">
        <img src="{{ asset('images/mawasa-logo.png') }}" alt="MAWASA Logo" 
             class="h-10 w-10 rounded-lg shadow-md">
        <div class="flex flex-col">
            <h1 class="font-semibold text-lg tracking-wide text-white dark:text-gray-100">MAWASA</h1>
            <p class="leading-tight text-[#d4f2ff] dark:text-gray-400 text-[11px]">
                Brgy. Manambulan, Tugbok District, Davao City
            </p>
        </div>
    </div>

    <nav class="flex-1 px-4 py-6">
        <p class="uppercase text-[#bfe8ff] dark:text-gray-500 mb-4 text-xs font-medium tracking-wider">
            Admin Menu
        </p>
        <ul class="space-y-2 text-sm">
            <li>
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg 
                          transition-all duration-200 ease-in-out font-medium 
                          hover:bg-[#008fde]/60 dark:hover:bg-gray-800 
                          {{ request()->routeIs('admin.dashboard') ? 'bg-[#008fde]/50 dark:bg-gray-800 text-white' : '' }}">
                    <x-heroicon-o-home class="w-5 h-5 text-[#d4f2ff] dark:text-gray-400" />
                    <span>Main</span>
                </a>
            </li>
            <li x-data="{ openCustomers: {{ request()->routeIs('admin.customers', 'admin.applicants.*') ? 'true' : 'false' }} }">
                <button @click="openCustomers = !openCustomers"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg 
                               hover:bg-[#008fde]/60 dark:hover:bg-gray-800 transition-all duration-200 ease-in-out font-medium">
                    <span class="flex items-center gap-3">
                        <x-heroicon-o-user-group class="w-5 h-5 text-[#d4f2ff] dark:text-gray-400" />
                        <span>Customers</span>
                    </span>
                    <svg :class="openCustomers ? 'rotate-180' : ''"
                         class="w-4 h-4 transform transition-all duration-200 ease-in-out"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <ul x-show="openCustomers" x-transition
                    class="mt-2 ml-6 space-y-1 bg-[#007fce]/95 dark:bg-gray-950 
                           border border-[#0085DC] dark:border-gray-800 
                           rounded-lg shadow-lg overflow-hidden">
                    <li>
                        <a href="{{ route('admin.customers') }}"
                           class="block px-3 py-2 hover:bg-[#008fde]/70 dark:hover:bg-gray-800 
                                  transition-all duration-200 ease-in-out rounded-md 
                                  {{ request()->routeIs('admin.customers') ? 'bg-[#008fde]/50 dark:bg-gray-800 text-white' : '' }}">
                            Customer Management
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.applicants.index') }}"
                           class="block px-3 py-2 hover:bg-[#008fde]/70 dark:hover:bg-gray-800 
                                  transition-all duration-200 ease-in-out rounded-md 
                                  {{ request()->routeIs('admin.applicants.*') ? 'bg-[#008fde]/50 dark:bg-gray-800 text-white' : '' }}">
                            New Applicants
                        </a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="{{ route('admin.meters') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg 
                          hover:bg-[#008fde]/60 dark:hover:bg-gray-800 
                          transition-all duration-200 ease-in-out font-medium 
                          {{ request()->routeIs('admin.meters') ? 'bg-[#008fde]/50 dark:bg-gray-800 text-white' : '' }}">
                    <x-heroicon-o-adjustments-horizontal class="w-5 h-5 text-[#d4f2ff] dark:text-gray-400" />
                    <span>Meter Management</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.billing') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg 
                          hover:bg-[#008fde]/60 dark:hover:bg-gray-800 
                          transition-all duration-200 ease-in-out font-medium 
                          {{ request()->routeIs('admin.billing') ? 'bg-[#008fde]/50 dark:bg-gray-800 text-white' : '' }}">
                    <x-heroicon-o-credit-card class="w-5 h-5 text-[#d4f2ff] dark:text-gray-400" />
                    <span>Billing Management</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.billing.archived') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg 
                          hover:bg-[#008fde]/60 dark:hover:bg-gray-800 
                          transition-all duration-200 ease-in-out font-medium 
                          {{ request()->routeIs('admin.billing.archived') ? 'bg-[#008fde]/50 dark:bg-gray-800 text-white' : '' }}">
                    <x-heroicon-o-archive-box class="w-5 h-5 text-[#d4f2ff] dark:text-gray-400" />
                    <span>Archived Billing</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.notices') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg 
                          hover:bg-[#008fde]/60 dark:hover:bg-gray-800 
                          transition-all duration-200 ease-in-out font-medium 
                          {{ request()->routeIs('admin.notices') ? 'bg-[#008fde]/50 dark:bg-gray-800 text-white' : '' }}">
                    <x-heroicon-o-bell class="w-5 h-5 text-[#d4f2ff] dark:text-gray-400" />
                    <span>Notice to Staff</span>
                </a>
            </li>

            {{-- Reports dropdown --}}
            <li x-data="{ openReports: {{ request()->routeIs('admin.reports.revenue') ? 'true' : 'false' }} }" class="relative">
                <button @click="openReports = !openReports"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg 
                               hover:bg-[#008fde]/60 dark:hover:bg-gray-800 transition-all duration-200 ease-in-out font-medium">
                    <span class="flex items-center gap-3">
                        <x-heroicon-o-chart-bar class="w-5 h-5 text-[#d4f2ff] dark:text-gray-400" />
                        <span>Reports</span>
                    </span>
                    <svg :class="openReports ? 'rotate-180' : ''"
                         class="w-4 h-4 transform transition-all duration-200 ease-in-out"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <ul x-show="openReports" x-transition
                    class="mt-2 ml-6 space-y-1 bg-[#007fce]/95 dark:bg-gray-950 
                           border border-[#0085DC] dark:border-gray-800 
                           rounded-lg shadow-lg overflow-hidden">
                    <li>
                        <a href="{{ route('admin.reports.revenue', ['view' => 'payments']) }}"
                           class="block px-3 py-2 hover:bg-[#008fde]/70 dark:hover:bg-gray-800 
                                  transition-all duration-200 ease-in-out rounded-md 
                                  {{ request()->routeIs('admin.reports.revenue') && request('view', 'payments') === 'payments' ? 'bg-[#008fde]/50 dark:bg-gray-800 text-white' : '' }}">
                           Customer Payment Report
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.reports.revenue', ['view' => 'issues']) }}"
                           class="block px-3 py-2 hover:bg-[#008fde]/70 dark:hover:bg-gray-800 
                                  transition-all duration-200 ease-in-out rounded-md 
                                  {{ request()->routeIs('admin.reports.revenue') && request('view') === 'issues' ? 'bg-[#008fde]/50 dark:bg-gray-800 text-white' : '' }}">
                           Issue Report
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.reports.revenue', ['view' => 'print']) }}"
                           class="block px-3 py-2 hover:bg-[#008fde]/70 dark:hover:bg-gray-800 
                                  transition-all duration-200 ease-in-out rounded-md 
                                  {{ request()->routeIs('admin.reports.revenue') && request('view') === 'print' ? 'bg-[#008fde]/50 dark:bg-gray-800 text-white' : '' }}">
                           Print Reports
                        </a>
                    </li>
                </ul>
            </li>

            {{-- Complaints (Issue Reports) --}}
            <li>
                <a href="{{ route('admin.reports') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg 
                          hover:bg-[#008fde]/60 dark:hover:bg-gray-800 
                          transition-all duration-200 ease-in-out font-medium 
                          {{ request()->routeIs('admin.reports') ? 'bg-[#008fde]/50 dark:bg-gray-800 text-white' : '' }}">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-yellow-400" />
                    <span>Issue Complaints</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.activity-log') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg 
                          hover:bg-[#008fde]/60 dark:hover:bg-gray-800 
                          transition-all duration-200 ease-in-out font-medium 
                          {{ request()->routeIs('admin.activity-log') ? 'bg-[#008fde]/50 dark:bg-gray-800 text-white' : '' }}">
                    <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-[#d4f2ff] dark:text-gray-400" />
                    <span>Activity Log</span>
                </a>
            </li>
        </ul>
    </nav>
</div>
