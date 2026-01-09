<div class="rounded-lg border border-gray-200 bg-white p-4 shadow dark:border-gray-700 dark:bg-gray-800">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <span class="text-xl">🎂</span>
            {{ __('Upcoming Birthdays') }}
        </h3>
    </div>

    @if($birthdays->isEmpty())
        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
            {{ __('No upcoming birthdays in the next 7 days.') }}
        </p>
    @else
        <ul class="space-y-3">
            @foreach($birthdays as $user)
                <li class="flex items-center gap-3">
                    <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-full object-cover">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $user->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $user->next_birthday->translatedFormat('d M') }}
                            @if($user->days_until == 0)
                                <span class="text-green-600 dark:text-green-400 font-semibold">🎉 {{ __('Today!') }}</span>
                            @elseif($user->days_until == 1)
                                <span class="text-blue-600 dark:text-blue-400">{{ __('Tomorrow') }}</span>
                            @else
                                <span class="text-gray-400">({{ $user->days_until }} {{ __('days') }})</span>
                            @endif
                        </p>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
