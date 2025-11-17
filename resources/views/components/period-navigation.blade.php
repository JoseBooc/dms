<div class="flex items-center justify-between bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border">
    <button 
        wire:click="navigatePeriod('previous')"
        type="button"
        class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
        {{ !$canGoPrevious ? 'disabled' : '' }}
    >
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Previous
    </button>
    
    <div class="flex-1 text-center">
        <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">
            {{ $display }}
        </span>
        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            {{ ucfirst($period) }} View
        </div>
    </div>
    
    <button 
        wire:click="navigatePeriod('next')"
        type="button"
        class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
        {{ !$canGoNext ? 'disabled' : '' }}
    >
        Next
        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
    </button>
</div>