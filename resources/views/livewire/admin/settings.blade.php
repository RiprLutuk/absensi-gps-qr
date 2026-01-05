<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Application Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-xl sm:rounded-lg">
                <div class="p-4 lg:p-6">
                    
                    <div class="mb-4 flex-col items-center gap-5 sm:flex-row md:flex md:justify-between lg:mr-4">
                        <h3 class="mb-4 text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200 md:mb-0">
                            Generic Settings
                        </h3>
                    </div>

                    <div class="space-y-6">
                        @foreach($groups as $group => $settings)
                            <div class="p-4 sm:p-8 bg-gray-50 dark:bg-gray-700/50 shadow-sm sm:rounded-lg border border-gray-100 dark:border-gray-700">
                                <header class="mb-6 border-b border-gray-200 dark:border-gray-700 pb-2">
                                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 uppercase tracking-wider">
                                        {{ $group }}
                                    </h2>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        {{ __('Manage configuration for') }} {{ $group }}
                                    </p>
                                </header>
                                
                                <div class="grid gap-6">
                                    @foreach($settings as $setting)
                                        <div class="grid gap-2" wire:key="setting-{{ $setting->id }}">
                                            <x-label :for="'setting_' . $this->getId() . '_' . $setting->id" :value="$setting->description ?? $setting->key" />
                                            <div class="relative">
                                                <x-input 
                                                    :id="'setting_' . $this->getId() . '_' . $setting->id" 
                                                    :type="$setting->type === 'number' ? 'number' : 'text'" 
                                                    class="mt-1 block w-full disabled:bg-gray-100 disabled:text-gray-500 dark:disabled:bg-gray-700 dark:disabled:text-gray-400" 
                                                    :value="$setting->value"
                                                    autocomplete="off"
                                                    wire:change="updateValue({{ $setting->id }}, $event.target.value)"
                                                    :disabled="!auth()->user()->isSuperadmin"
                                                />
                                                <div class="mt-1 flex items-center justify-between">
                                                    <span class="text-xs font-mono text-gray-400 dark:text-gray-500">{{ $setting->key }}</span>
                                                    <span class="text-xs text-blue-500" wire:loading wire:target="updateValue">
                                                        Saving...
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
