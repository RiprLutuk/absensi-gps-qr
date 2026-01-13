@php
    use Illuminate\Support\Carbon;
    $isPerDayFilter = $startDate === $endDate;
    $showUserDetail = true; 
@endphp
<div>
    <div class="mx-auto max-w-7xl px-2 sm:px-0 lg:px-0">
        @pushOnce('styles')
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
                integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        @endpushOnce

        <!-- Header -->
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                    {{ __('Attendance Data') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Monitor employee attendance, shifts, and status.') }}
                </p>
            </div>
             <x-secondary-button
                href="{{ route('admin.attendances.report', ['startDate' => $startDate, 'endDate' => $endDate, 'division' => $division, 'jobTitle' => $jobTitle]) }}"
                class="flex justify-center w-full sm:w-auto gap-2">
                <x-heroicon-o-printer class="h-5 w-5" />
                {{ __('Export Report') }}
            </x-secondary-button>
        </div>

        <!-- Filters -->
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5 items-end">
            <!-- Date Range -->
            <div class="col-span-1">
                 <x-label for="start_date" value="{{ __('Start Date') }}" />
                <x-input type="date" id="start_date" wire:model.live="startDate" class="mt-1 w-full" />
            </div>
             <div class="col-span-1">
                 <x-label for="end_date" value="{{ __('End Date') }}" />
                <x-input type="date" id="end_date" wire:model.live="endDate" class="mt-1 w-full" />
            </div>

            <!-- Division -->
            <div class="col-span-1">
                 <x-label value="{{ __('Division') }}" class="mb-1" />
                 <x-tom-select id="filter_division" wire:model.live="division" placeholder="{{ __('All') }}"
                    :options="\App\Models\Division::all()->map(fn($d) => ['id' => $d->id, 'name' => $d->name])" />
            </div>

            <!-- Job Title -->
             <div class="col-span-1">
                 <x-label value="{{ __('Job Title') }}" class="mb-1" />
                <x-tom-select id="filter_jobTitle" wire:model.live="jobTitle" placeholder="{{ __('All') }}"
                    :options="\App\Models\JobTitle::all()->map(fn($j) => ['id' => $j->id, 'name' => $j->name])" />
            </div>

            <!-- Search -->
            <div class="col-span-1 relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <x-heroicon-m-magnifying-glass class="h-5 w-5 text-gray-400" />
                </div>
                 <input type="text" wire:model.live.debounce.500ms="search"
                    placeholder="{{ __('Search...') }}" 
                     class="block w-full rounded-lg border-0 py-2 pl-10 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700 sm:text-sm sm:leading-6">
            </div>
        </div>

        <!-- Content -->
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <!-- Desktop Table -->
            <div class="hidden sm:block overflow-x-auto">
                 <table class="w-full whitespace-nowrap text-left text-sm">
                    <thead class="bg-gray-50 text-gray-500 dark:bg-gray-700/50 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-medium">{{ __('Employee') }}</th>
                            @if ($showUserDetail)
                                <th scope="col" class="px-6 py-4 font-medium">{{ __('NIP') }}</th>
                                <th scope="col" class="px-6 py-4 font-medium">{{ __('Division') }}</th>
                                <th scope="col" class="px-6 py-4 font-medium">{{ __('Job Title') }}</th>
                                @if ($isPerDayFilter)
                                    <th scope="col" class="px-6 py-4 font-medium">{{ __('Shift') }}</th>
                                @endif
                            @endif

                             @foreach ($dates as $date)
                                @php
                                    $textClass = (!$isPerDayFilter && ($date->isSunday() || $date->isFriday())) 
                                        ? ($date->isSunday() ? 'text-red-500 font-bold' : 'text-green-600 font-bold') 
                                        : 'text-gray-900 dark:text-white';
                                @endphp
                                <th scope="col" class="px-2 py-4 text-center font-medium border-l border-gray-100 dark:border-gray-700 {{ $textClass }}">
                                     @if ($isPerDayFilter)
                                        {{ __('Status') }}
                                    @else
                                        {{ $date->format('d/m') }}
                                    @endif
                                </th>
                            @endforeach

                             @if ($isPerDayFilter)
                                <th scope="col" class="px-6 py-4 font-medium">{{ __('Time In') }}</th>
                                <th scope="col" class="px-6 py-4 font-medium">{{ __('Time Out') }}</th>
                            @endif

                            @if (!$isPerDayFilter)
                                 @foreach (['H', 'T', 'I', 'S', 'A'] as $_st)
                                    <th scope="col" class="px-2 py-4 text-center font-medium border-l border-gray-100 dark:border-gray-700">{{ __($_st) }}</th>
                                @endforeach
                            @endif

                             @if ($isPerDayFilter)
                                <th scope="col" class="px-6 py-4 font-medium text-right">{{ __('Actions') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                         @forelse ($employees as $employee)
                            @php $attendances = $employee->attendances; @endphp
                            <tr wire:key="{{ $employee->id }}" class="group hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                    {{ $employee->name }}
                                </td>
                                 @if ($showUserDetail)
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $employee->nip }}</td>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $employee->division?->name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $employee->jobTitle?->name ?? '-' }}</td>
                                    @if ($isPerDayFilter)
                                         @php
                                            $attendance = $employee->attendances->first();
                                        @endphp
                                        <td class="px-6 py-4 text-gray-900 dark:text-white">{{ $attendance['shift'] ?? '-' }}</td>
                                    @endif
                                @endif

                                @php
                                    $presentCount = 0; $lateCount = 0; $excusedCount = 0; $sickCount = 0; $absentCount = 0;
                                @endphp

                                @foreach ($dates as $date)
                                    @php
                                        $attendance = $attendances->firstWhere(fn($v) => \Carbon\Carbon::parse($v['date'])->isSameDay($date));
                                        $status = ($attendance ?? ['status' => ($date->isWeekend() || !$date->isPast() ? '-' : 'absent')])['status'];
                                        
                                        $cellClass = match($status) {
                                            'present' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                            'late' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                            'excused' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                            'sick' => 'bg-gray-50 text-gray-700 dark:bg-gray-700/50 dark:text-gray-400',
                                            'absent' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                            default => 'text-gray-400'
                                        };

                                        // Count stats
                                        switch ($status) {
                                            case 'present': $presentCount++; $short = 'H'; break;
                                            case 'late': $lateCount++; $short = 'T'; break;
                                            case 'excused': $excusedCount++; $short = 'I'; break;
                                            case 'sick': $sickCount++; $short = 'S'; break;
                                            case 'absent': $absentCount++; $short = 'A'; break;
                                            default: $short = '-'; break;
                                        }
                                    @endphp
                                    
                                     <td class="px-2 py-4 text-center border-l border-gray-100 dark:border-gray-700">
                                        @if($attendance && ($attendance['attachment'] || $attendance['coordinates']))
                                            <button wire:click="show({{ $attendance['id'] }})" class="w-full h-full rounded {{ $cellClass }} font-medium hover:ring-2 ring-inset ring-primary-500 transition-all">
                                                {{ $isPerDayFilter ? __($status) : $short }}
                                            </button>
                                        @else
                                            <span class="inline-block w-full rounded {{ $cellClass }} font-medium">
                                                {{ $isPerDayFilter ? __($status) : $short }}
                                            </span>
                                        @endif
                                    </td>
                                @endforeach

                                @if ($isPerDayFilter)
                                     <td class="px-6 py-4 text-gray-900 dark:text-white">{{ $timeIn ?? '-' }}</td>
                                     <td class="px-6 py-4 text-gray-900 dark:text-white">{{ $timeOut ?? '-' }}</td>
                                     <td class="px-6 py-4 text-right">
                                         @if ($attendance && ($attendance['attachment'] || $attendance['coordinates']))
                                            <div class="flex justify-end">
                                                <button wire:click="show({{ $attendance['id'] }})" class="text-gray-400 hover:text-primary-600 transition-colors" title="{{ __('View Details') }}">
                                                    <x-heroicon-m-eye class="h-5 w-5" />
                                                </button>
                                            </div>
                                         @else
                                            <span class="text-gray-400">-</span>
                                         @endif
                                    </td>
                                @endif

                                @if (!$isPerDayFilter)
                                    @foreach ([$presentCount, $lateCount, $excusedCount, $sickCount, $absentCount] as $count)
                                        <td class="px-2 py-4 text-center border-l border-gray-100 dark:border-gray-700 font-medium text-gray-700 dark:text-gray-300">
                                            {{ $count }}
                                        </td>
                                    @endforeach
                                @endif

                            </tr>
                        @empty
                             <tr>
                                <td colspan="{{ count($dates) + ($isPerDayFilter ? 8 : 10) }}" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <x-heroicon-o-calendar class="h-12 w-12 text-gray-300 dark:text-gray-600 mb-3" />
                                        <p class="font-medium">{{ __('No attendance records found') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                 </table>
            </div>

            <!-- Mobile Card View (Optimized) -->
             <div class="grid grid-cols-1 sm:hidden divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($employees as $employee)
                    <div class="p-4">
                        <div class="flex justify-between items-start mb-2">
                             <div>
                                <h4 class="font-bold text-gray-900 dark:text-white">{{ $employee->name }}</h4>
                                <p class="text-xs text-gray-500">{{ $employee->division?->name }} • {{ $employee->jobTitle?->name }}</p>
                             </div>
                             @if($isPerDayFilter)
                                @php 
                                    $att = $employee->attendances->first(); 
                                    $status = $att['status'] ?? ($startDate == $endDate && \Carbon\Carbon::parse($startDate)->isPast() && !\Carbon\Carbon::parse($startDate)->isWeekend() ? 'absent' : '-');
                                    $color = match($status) {
                                        'present' => 'bg-green-100 text-green-800', 'late' => 'bg-amber-100 text-amber-800', 'absent' => 'bg-red-100 text-red-800', default => 'bg-gray-100 text-gray-800'
                                    };
                                @endphp
                                <span class="px-2 py-1 rounded text-xs font-bold uppercase {{ $color }}">{{ $status }}</span>
                             @endif
                        </div>

                        <!-- Mini Stats for Range View -->
                        @if(!$isPerDayFilter)
                             @php
                                $attendances = $employee->attendances;
                                $p=0; $l=0; $a=0;
                                foreach($dates as $d) {
                                    $s = ($attendances->firstWhere(fn($v)=>\Carbon\Carbon::parse($v['date'])->isSameDay($d)) ?? ['status'=>'absent'])['status'];
                                    if($s=='present') $p++; elseif($s=='late') $l++; elseif($s=='absent') $a++;
                                }
                             @endphp
                             <div class="grid grid-cols-3 gap-2 mt-3 text-center">
                                <div class="bg-green-50 p-1.5 rounded text-xs">
                                    <span class="block font-bold text-green-700">{{ $p }}</span>
                                    <span class="text-green-600">Hadir</span>
                                </div>
                                <div class="bg-amber-50 p-1.5 rounded text-xs">
                                    <span class="block font-bold text-amber-700">{{ $l }}</span>
                                    <span class="text-amber-600">Telat</span>
                                </div>
                                 <div class="bg-red-50 p-1.5 rounded text-xs">
                                    <span class="block font-bold text-red-700">{{ $a }}</span>
                                    <span class="text-red-600">Absen</span>
                                </div>
                             </div>
                        @else
                             <!-- Detail for Single Day -->
                             <div class="grid grid-cols-2 gap-4 mt-3 text-sm">
                                <div>
                                    <span class="text-gray-500 text-xs block">Masuk</span>
                                    <span class="font-mono text-gray-900 dark:text-white">{{ $att['time_in'] ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 text-xs block">Pulang</span>
                                    <span class="font-mono text-gray-900 dark:text-white">{{ $att['time_out'] ?? '-' }}</span>
                                </div>
                             </div>
                             @if($att && ($att['attachment'] || $att['coordinates']))
                                <button wire:click="show({{ $att['id'] }})" class="mt-3 w-full py-2 bg-gray-50 text-gray-600 rounded text-sm font-medium hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300">
                                    View Details
                                </button>
                             @endif
                        @endif
                    </div>
                @endforeach
            </div>
            
             @if($employees->hasPages())
                <div class="border-t border-gray-200 bg-gray-50 px-6 py-3 dark:border-gray-700 dark:bg-gray-800">
                    {{ $employees->links() }}
                </div>
            @endif
        </div>
    </div>
    
    <x-attendance-detail-modal :current-attendance="$currentAttendance" />
    @stack('attendance-detail-scripts')
</div>
