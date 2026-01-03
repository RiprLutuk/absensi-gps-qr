<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Employee') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl dark:bg-gray-800 rounded-lg sm:rounded-lg">
                <div class="p-4 lg:p-6">
                    @livewire('admin.employee-component')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
