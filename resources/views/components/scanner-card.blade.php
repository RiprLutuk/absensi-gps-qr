<div class="rounded-lg border border-gray-200 bg-white p-4 sm:p-6 shadow dark:border-gray-700 dark:bg-gray-800" id="scanner-card" wire:ignore>

    <div class="flex justify-between items-center mb-4">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-white">
            {{ $title }}
        </h3>
        <button type="button" id="switch-camera-btn" onclick="window.switchCamera?.()" class="text-xs flex items-center gap-1 text-blue-600 dark:text-blue-400 hover:underline">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Switch Camera
        </button>
    </div>

    <div class="scanner-container w-full min-h-[400px] aspect-square sm:aspect-video rounded-2xl bg-gray-100 dark:bg-gray-900
                cursor-pointer flex items-center justify-center overflow-hidden relative"
        id="scanner" onclick="handleScanClick()">

        <span id="scanner-placeholder" class="text-gray-600 dark:text-gray-300">
            Tap to scan
        </span>
    </div>

    <div id="scanner-result" class="hidden mt-3 text-green-600 dark:text-green-400 font-medium">
    </div>

    <div id="scanner-error" class="hidden mt-3 text-red-600 dark:text-red-400 font-medium">
    </div>

    <style>
        #scanner video {
            transform: scaleX(-1) !important;
        }
    </style>
</div>
