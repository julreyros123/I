{{-- Sidebar --}}
<div 
    x-data="{ open: false }"
    class="w-64 bg-gradient-to-b from-blue-900 to-blue-800 
           text-gray-100 dark:from-gray-950 dark:to-gray-900 
           dark:text-gray-200 min-h-screen flex flex-col 
           fixed left-0 top-0 z-20 font-inter"
    id="sidebar"
    x-cloak
>
    {{-- Logo / Header --}}
    <div class="p-6 border-b border-blue-700 dark:border-gray-800 flex items-center space-x-3">
        <img src="{{ asset('images/mawasa-logo.png') }}" alt="MAWASA Logo" 
             class="h-14 w-14 rounded-lg shadow-md">
        <div class="flex flex-col">
            <h1 class="font-semibold text-lg tracking-wide text-white dark:text-gray-100">MAWASA</h1>
            <p class="leading-tight text-blue-200 dark:text-gray-400 text-xs">
                Brgy. Manambulan Tugbok District, Davao City
            </p>
        </div>
    </div>

    {{-- Menu --}}
    <nav class="flex-1 px-4 py-6">
        <p class="uppercase text-blue-300 dark:text-gray-500 mb-4 text-xs font-medium tracking-wider">
            Menu
        </p>
    
        <ul class="space-y-2 text-sm">
            {{-- Dashboard --}}
            <li>
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg 
                          hover:bg-blue-700/60 dark:hover:bg-gray-800 
                          transition-all duration-200 ease-in-out font-medium">
                    <x-heroicon-o-home class="w-5 h-5 text-blue-300 dark:text-gray-400" />
                    <span>Dashboard</span>
                </a>
            </li>

            {{-- Admin (visible to admins only) --}}
            @auth
                @if(optional(auth()->user())->role === 'admin')
                <li>
                    <a href="{{ route('admin.dashboard') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg 
                              hover:bg-blue-700/60 dark:hover:bg-gray-800 
                              transition-all duration-200 ease-in-out font-medium">
                        <x-heroicon-o-cog-6-tooth class="w-5 h-5 text-blue-300 dark:text-gray-400" />
                        <span>Admin</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.notices') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg 
                              hover:bg-blue-700/60 dark:hover:bg-gray-800 
                              transition-all duration-200 ease-in-out font-medium">
                        <x-heroicon-o-bell class="w-5 h-5 text-blue-300 dark:text-gray-400" />
                        <span>Notice to Staff</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.reports') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg 
                              hover:bg-blue-700/60 dark:hover:bg-gray-800 
                              transition-all duration-200 ease-in-out font-medium">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-yellow-400" />
                        <span>Issue Complaints</span>
                    </a>
                </li>
                @endif
            @endauth

            {{-- Register --}}
            <li>
                @php($registerUrl = \Illuminate\Support\Facades\Route::has('register.index') ? route('register.index') : url('/register'))
                <a href="{{ $registerUrl }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg 
                          hover:bg-blue-700/60 dark:hover:bg-gray-800 
                          transition-all duration-200 ease-in-out font-medium">
                    <x-heroicon-o-pencil-square class="w-5 h-5 text-blue-300 dark:text-gray-400" />
                    <span>Register</span>
                </a>
            </li>

            {{-- Customer --}}
            <li>
                <a href="{{ route('customer.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg 
                          hover:bg-blue-700/60 dark:hover:bg-gray-800 
                          transition-all duration-200 ease-in-out font-medium">
                    <x-heroicon-o-user-group class="w-5 h-5 text-blue-300 dark:text-gray-400" />
                    <span>Customer</span>
                </a>
            </li>

            {{-- Records Dropdown --}}
            <li class="relative">
                <button @click="open = !open"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg 
                               hover:bg-blue-700/60 dark:hover:bg-gray-800 transition-all duration-200 ease-in-out font-medium">
                    <span class="flex items-center gap-3">
                        <x-heroicon-o-document-text class="w-5 h-5 text-blue-300 dark:text-gray-400" />
                        <span>Records</span>
                    </span>
                    <svg :class="open ? 'rotate-180' : ''"
                         class="w-4 h-4 transform transition-all duration-200 ease-in-out"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                {{-- Dropdown --}}
                <ul x-show="open" x-transition
                    class="mt-2 ml-6 space-y-1 bg-blue-800/95 dark:bg-gray-950 
                           border border-blue-700 dark:border-gray-800 
                           rounded-lg shadow-lg overflow-hidden">
                    <li>
                        <a href="{{ route('records.billing') }}"
                           class="block px-3 py-2 hover:bg-blue-700/70 dark:hover:bg-gray-800 
                                  transition-all duration-200 ease-in-out rounded-md">
                           Billing Records
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('records.payments') }}"
                           class="block px-3 py-2 hover:bg-blue-700/70 dark:hover:bg-gray-800 
                                  transition-all duration-200 ease-in-out rounded-md">
                           Payment Records
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>

   {{-- Report Issue --}}
<div x-data="{ showModal: false, category: '' }" class="p-4 border-t border-blue-700 dark:border-gray-800">
    <!-- Trigger Button -->
    <button @click="showModal = true"
            class="w-full flex items-center gap-3 px-3 py-2 rounded-lg 
                   hover:bg-blue-700/60 dark:hover:bg-gray-800 
                   transition-all duration-200 ease-in-out font-medium text-sm">
        <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-yellow-400 dark:text-yellow-500" />
        <span>Report Issue</span>
    </button>

    <!-- Modal Overlay -->
    <div x-show="showModal"
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
         x-transition>
         
        <!-- Modal Box -->
        <div @click.away="showModal = false"
             class="bg-white dark:bg-gray-900 rounded-xl shadow-lg w-full max-w-md p-6">
             
            <h2 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">
                Report an Issue
            </h2>

            <!-- ✅ Report Issue Form -->
            <form method="POST" action="{{ route('reports.store') }}" class="space-y-3 text-sm">
                @csrf

                <div>
                    <span class="block text-xs font-medium text-gray-800 dark:text-gray-300 mb-1">Problem category</span>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2 text-gray-800 dark:text-gray-200">
                            <input type="radio" name="category" value="UI bug" x-model="category" class="text-blue-600 focus:ring-blue-500">
                            <span>UI bug</span>
                        </label>
                        <label class="flex items-center gap-2 text-gray-800 dark:text-gray-200">
                            <input type="radio" name="category" value="Delay issue" x-model="category" class="text-blue-600 focus:ring-blue-500">
                            <span>Delay issue</span>
                        </label>
                        <label class="flex items-center gap-2 text-gray-800 dark:text-gray-200">
                            <input type="radio" name="category" value="Billing problem" x-model="category" class="text-blue-600 focus:ring-blue-500">
                            <span>Billing problem</span>
                        </label>
                        <label class="flex items-center gap-2 text-gray-800 dark:text-gray-200">
                            <input type="radio" name="category" value="Other" x-model="category" class="text-blue-600 focus:ring-blue-500">
                            <span>Other</span>
                        </label>
                    </div>
                </div>

                <div x-show="category === 'Other'" x-cloak>
                    <label for="other_problem" class="block text-xs font-medium text-gray-800 dark:text-gray-300 mb-1">Other problem</label>
                    <input type="text" id="other_problem" name="other_problem"
                           class="w-full border border-gray-300 dark:border-gray-700 rounded p-2 text-sm 
                                  text-gray-900 dark:text-gray-100 
                                  bg-white dark:bg-gray-800"
                           placeholder="Specify your problem">
                </div>

                <div>
                    <label for="message" class="block text-xs font-medium text-gray-800 dark:text-gray-300 mb-1">Describe the issue</label>
                    <textarea id="message" name="message" rows="4"
                              class="w-full border border-gray-300 dark:border-gray-700 rounded p-2 text-sm 
                                     text-gray-900 dark:text-gray-100 
                                     bg-white dark:bg-gray-800"
                              placeholder="Provide as much detail as possible..."></textarea>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" @click="showModal = false"
                            class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 
                                   hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 
                                   dark:text-gray-200 transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 
                                   text-white transition">
                        Submit
                    </button>
                </div>
            </form>
            <!-- ✅ End of Report Issue Form -->
        </div>
    </div>
</div>

</div>

