<div class="py-12">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                    {{ __('Leave Approvals') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Review and manage your team\'s leave requests.') }}
                </p>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="overflow-x-auto">
                <table class="w-full whitespace-nowrap text-left text-sm">
                    <thead class="bg-gray-50 text-gray-500 dark:bg-gray-700/50 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-medium">{{ __('Employee') }}</th>
                            <th scope="col" class="px-6 py-4 font-medium">{{ __('Date') }}</th>
                            <th scope="col" class="px-6 py-4 font-medium">{{ __('Type') }}</th>
                            <th scope="col" class="px-6 py-4 font-medium">{{ __('Note') }}</th>
                            <th scope="col" class="px-6 py-4 font-medium">{{ __('Attachment') }}</th>
                            <th scope="col" class="px-6 py-4 text-right font-medium">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($groupedLeaves as $groupKey => $group)
                            @php
                                $firstLeave = $group->first();
                                $lastLeave = $group->last();
                                $leaveIds = $group->pluck('id')->toArray();
                                // Format Date Range
                                if ($group->count() > 1) {
                                    if ($firstLeave->date->format('M Y') == $lastLeave->date->format('M Y')) {
                                        $dateDisplay = $firstLeave->date->format('d') . ' - ' . $lastLeave->date->format('d M Y') . ' (' . $group->count() . ' days)';
                                    } else {
                                        $dateDisplay = $firstLeave->date->format('d M') . ' - ' . $lastLeave->date->format('d M Y') . ' (' . $group->count() . ' days)';
                                    }
                                } else {
                                    $dateDisplay = $firstLeave->date->format('d M Y');
                                }
                            @endphp
                            <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-9 w-9 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                            <img src="{{ $firstLeave->user->profile_photo_url }}" alt="{{ $firstLeave->user->name }}" class="h-full w-full object-cover">
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-white">{{ $firstLeave->user->name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $firstLeave->user->jobTitle->name ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                    {{ $dateDisplay }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $firstLeave->status === 'sick' ? 'bg-yellow-50 text-yellow-800 ring-yellow-600/20 dark:bg-yellow-900/30 dark:text-yellow-400 dark:ring-yellow-500/50' : 'bg-blue-50 text-blue-700 ring-blue-700/10 dark:bg-blue-900/30 dark:text-blue-400 dark:ring-blue-500/50' }}">
                                        {{ __(ucfirst($firstLeave->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-300 max-w-xs truncate">
                                    {{ $firstLeave->note }}
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                    @if ($firstLeave->attachment)
                                        <a href="{{ $firstLeave->attachment_url }}" target="_blank" class="flex items-center gap-1 text-primary-600 hover:text-primary-700 transition-colors">
                                            <x-heroicon-m-paper-clip class="h-4 w-4" />
                                            <span>{{ __('View') }}</span>
                                        </a>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button wire:click="approve({{ json_encode($leaveIds) }})" class="text-gray-400 hover:text-green-600 transition-colors" title="{{ __('Approve') }}">
                                            <x-heroicon-m-check-circle class="h-6 w-6" />
                                        </button>
                                        <button wire:click="confirmReject({{ json_encode($leaveIds) }})" class="text-gray-400 hover:text-red-600 transition-colors" title="{{ __('Reject') }}">
                                            <x-heroicon-m-x-circle class="h-6 w-6" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <x-heroicon-o-inbox class="h-12 w-12 text-gray-300 dark:text-gray-600 mb-3" />
                                        <p class="font-medium">{{ __('No pending requests') }}</p>
                                        <p class="text-sm mt-1">{{ __('You\'re all caught up!') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Rejection Modal -->
        <x-dialog-modal wire:model.live="confirmingRejection">
            <x-slot name="title">
                {{ __('Reject Leave Request') }}
            </x-slot>

            <x-slot name="content">
                {{ __('Please provide a reason for rejecting this leave request.') }}

                <div class="mt-4">
                    <x-textarea wire:model="rejectionNote" placeholder="{{ __('Rejection Reason') }}"
                                class="block w-full" />
                    <x-input-error for="rejectionNote" class="mt-2" />
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="$toggle('confirmingRejection')" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3" wire:click="reject" wire:loading.attr="disabled">
                    {{ __('Reject Request') }}
                </x-danger-button>
            </x-slot>
        </x-dialog-modal>
    </div>
</div>
