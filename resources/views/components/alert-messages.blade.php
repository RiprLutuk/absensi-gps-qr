<div class="space-y-3">
    <div id="scanner-error"
        class="hidden text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 px-4 py-3 rounded-lg"
        wire:ignore></div>
    <div id="scanner-result"
        class="hidden text-sm font-medium text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 px-4 py-3 rounded-lg">
        {{ $successMsg }}
    </div>
</div>
