<div class="py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                    {{ __('Application Settings') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Configure global application settings and preferences.') }}
                </p>
            </div>
        </div>

        <!-- Content -->
        <div class="space-y-6">
            @foreach($groups as $group => $settings)
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-900/5 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-700/50">
                        <h3 class="text-base font-semibold leading-7 text-gray-900 dark:text-gray-100 uppercase tracking-wider">
                            {{ $group }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Configuration for') }} {{ $group }}
                        </p>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            @foreach($settings as $setting)
                                <div class="sm:col-span-4" wire:key="setting-{{ $setting->id }}">
                                    <div class="flex items-center justify-between mb-2">
                                        <x-label :for="'setting_' . $this->getId() . '_' . $setting->id" :value="$setting->description ?? $setting->key" />
                                        <span class="text-xs font-mono text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">{{ $setting->key }}</span>
                                    </div>

                                    <div class="relative">
                                        @if($setting->type === 'boolean')
                                            <button 
                                                type="button" 
                                                wire:click="updateValue({{ $setting->id }}, {{ $setting->value == '1' ? '0' : '1' }})" 
                                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 {{ $setting->value == '1' ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700' }}"
                                                {{ !auth()->user()->isSuperadmin ? 'disabled' : '' }}
                                            >
                                                <span class="sr-only">{{ $setting->description }}</span>
                                                <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $setting->value == '1' ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                            </button>
                                            <span class="ml-3 text-sm text-gray-900 dark:text-gray-300">
                                                {{ $setting->value == '1' ? __('Enabled') : __('Disabled') }}
                                            </span>

                                        @elseif($setting->type === 'select' && $setting->key === 'app.time_format')
                                            <select 
                                                id="setting_{{ $this->getId() }}_{{ $setting->id }}"
                                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 sm:text-sm"
                                                wire:change="updateValue({{ $setting->id }}, $event.target.value)"
                                                {{ !auth()->user()->isSuperadmin ? 'disabled' : '' }}
                                            >
                                                <option value="24" @selected($setting->value == '24')>{{ __('24 Hour (17:00)') }}</option>
                                                <option value="12" @selected($setting->value == '12')>{{ __('12 Hour (05:00 PM)') }}</option>
                                            </select>

                                        @else
                                            <x-input 
                                                :id="'setting_' . $this->getId() . '_' . $setting->id" 
                                                :type="$setting->type === 'number' ? 'number' : 'text'" 
                                                class="mt-1 block w-full disabled:bg-gray-100 disabled:text-gray-500 dark:disabled:bg-gray-700 dark:disabled:text-gray-400" 
                                                :value="$setting->value"
                                                autocomplete="off"
                                                wire:change="updateValue({{ $setting->id }}, $event.target.value)"
                                                :disabled="!auth()->user()->isSuperadmin"
                                            />
                                        @endif

                                        <div class="mt-2 flex items-center h-4">
                                            <span class="text-xs text-primary-600 dark:text-primary-400 font-medium opacity-0 transition-opacity duration-300" 
                                                  wire:loading.class.remove="opacity-0" 
                                                  wire:target="updateValue({{ $setting->id }})">
                                                <div class="flex items-center gap-1">
                                                    <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    <span>{{ __('Saving...') }}</span>
                                                </div>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
