<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Attendance') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="card">
                <div class="p-4 lg:p-6">
                    <div class="mb-4">
                        <x-secondary-button href="{{ url()->previous() }}">
                            <x-heroicon-o-chevron-left class="mr-2 h-5 w-5" />
                            Kembali
                        </x-secondary-button>
                    </div>
                    @livewire('attendance-history-component')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
