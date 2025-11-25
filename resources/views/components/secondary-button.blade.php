<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center gap-2 px-3.5 py-2.5 rounded-lg text-sm font-medium shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-400 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700']) }}>
    {{ $slot }}
</button>
