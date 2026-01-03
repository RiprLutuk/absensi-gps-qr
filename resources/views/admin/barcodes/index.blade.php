<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Barcode') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white p-4 shadow-xl dark:bg-gray-800 rounded-lg sm:rounded-lg">
                @livewire('admin.barcode-component')
            </div>
        </div>
    </div>
</x-app-layout>
