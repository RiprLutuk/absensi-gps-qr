@php
    use Illuminate\Support\Carbon;
    $m = Carbon::parse($month);
    $showUserDetail = !$month || $week || $date; // is week or day filter
    $isPerDayFilter = isset($date);
@endphp
<div>
    @pushOnce('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    @endpushOnce
    <h3 class="col-span-2 mb-4 text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
        Data Absensi
    </h3>
    <div class="mb-1 text-sm dark:text-white">Filter:</div>
    <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 lg:flex lg:flex-wrap lg:items-end gap-4">
        <div class="flex flex-col gap-1 w-full lg:w-auto">
            <x-label for="month_filter" value="Per Bulan"></x-label>
            <x-input type="month" name="month_filter" id="month_filter" wire:model.live="month" class="w-full" />
        </div>
        <div class="flex flex-col gap-1 w-full lg:w-auto">
            <x-label for="week_filter" value="Per Minggu"></x-label>
            <x-input type="week" name="week_filter" id="week_filter" wire:model.live="week" class="w-full" />
        </div>
        <div class="flex flex-col gap-1 w-full lg:w-auto sm:col-span-2 lg:col-span-1">
            <x-label for="day_filter" value="Per Hari"></x-label>
            <x-input type="date" name="day_filter" id="day_filter" wire:model.live="date" class="w-full" />
        </div>
        <div class="w-full lg:w-48">
            <x-tom-select id="division" wire:model.live="division" placeholder="{{ __('Select Division') }}"
                :options="\App\Models\Division::all()->map(fn($d) => ['id' => $d->id, 'name' => $d->name])" />
        </div>
        <div class="w-full lg:w-48">
            <x-tom-select id="jobTitle" wire:model.live="jobTitle" placeholder="{{ __('Select Job Title') }}"
                :options="\App\Models\JobTitle::all()->map(fn($j) => ['id' => $j->id, 'name' => $j->name])" />
        </div>
        <div class="flex items-center gap-2 w-full lg:w-auto sm:col-span-2 lg:col-span-1">
            <div class="flex-1">
                <x-input type="text" class="w-full" name="search" id="seacrh" wire:model.live.debounce.500ms="search"
                    placeholder="{{ __('Search') }}" />
            </div>
            {{-- <x-button type="button" wire:click="$refresh" wire:loading.attr="disabled">{{ __('Search') }}</x-button> --}}
        </div>
         <div class="w-full lg:w-auto sm:col-span-2 lg:col-span-1 lg:ml-auto">
             <x-secondary-button
                href="{{ route('admin.attendances.report', ['month' => $month, 'week' => $week, 'date' => $date, 'division' => $division, 'jobTitle' => $jobTitle]) }}"
                class="flex justify-center w-full lg:w-auto gap-2">
                Cetak Laporan
                <x-heroicon-o-printer class="h-5 w-5" />
            </x-secondary-button>
         </div>
    </div>

    <!-- Mobile Card View -->
    <div class="grid grid-cols-1 gap-4 sm:hidden mb-4">
        @foreach ($employees as $employee)
            @php
                 $attendances = $employee->attendances;
            @endphp
            <div class="card p-4">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h4 class="text-base font-bold text-gray-900 dark:text-white">{{ $employee->name }}</h4>
                        @if ($showUserDetail)
                             <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $employee->nip }}</p>
                        @endif
                    </div>
                </div>
                 @if ($showUserDetail)
                    <div class="flex flex-wrap gap-2 mb-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                             {{ $employee->division?->name ?? '-' }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                             {{ $employee->jobTitle?->name ?? '-' }}
                        </span>
                    </div>
                @endif

                @if ($isPerDayFilter)
                    <!-- Day View Content -->
                     @php
                        $attendance = $employee->attendances->isEmpty() ? null : $employee->attendances->first();
                        $timeIn = $attendance ? $attendance['time_in'] : null;
                        $timeOut = $attendance ? $attendance['time_out'] : null;
                        
                         // Calculate status for day view
                         if ($attendance) {
                            $status = $attendance['status']; // Assuming status is set in controller/resource
                         } else {
                             $isWeekend = isset($date) ? Carbon::parse($date)->isWeekend() : false;
                             $status = $isWeekend ? '-' : 'absent';
                         }
                         
                         $statusColor = match($status) {
                            'present' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                            'late' => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300',
                            'excused' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                            'sick' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                            'absent' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                         };
                    @endphp
                    <div class="border-t border-gray-100 dark:border-gray-700 pt-3">
                         <div class="flex justify-between items-center mb-2">
                             <div class="text-sm">
                                 <span class="text-gray-500 block text-xs">Shift</span>
                                 <span class="font-medium dark:text-gray-200">{{ $attendance['shift'] ?? '-' }}</span>
                             </div>
                             <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                                 {{ __($status) }}
                             </span>
                         </div>
                         <div class="grid grid-cols-2 gap-4 text-sm mb-3">
                             <div>
                                 <span class="text-gray-500 block text-xs">Masuk</span>
                                 <span class="font-medium dark:text-gray-200">{{ $timeIn ?? '-' }}</span>
                             </div>
                             <div>
                                 <span class="text-gray-500 block text-xs">Keluar</span>
                                 <span class="font-medium dark:text-gray-200">{{ $timeOut ?? '-' }}</span>
                             </div>
                         </div>
                          @if ($attendance && ($attendance['attachment'] || $attendance['note'] || $attendance['coordinates']))
                            <x-button type="button" 
                                wire:click="show({{ $attendance['id'] }})"

                                class="w-full flex items-center justify-center">
                                {{ __('Detail') }}
                            </x-button>
                        @endif
                    </div>
                @else
                    <!-- Month/Week View Content -->
                    <div class="border-t border-gray-100 dark:border-gray-700 pt-3">
                         @php
                            $presentCount = 0;
                            $lateCount = 0;
                            $excusedCount = 0;
                            $sickCount = 0;
                            $absentCount = 0;
                            
                             foreach ($dates as $date) {
                                $attendance = $attendances->firstWhere(fn($v, $k) => \Carbon\Carbon::parse($v['date'])->isSameDay($date));
                                $isWeekend = $date->isWeekend();
                                $status = ($attendance ?? ['status' => $isWeekend || !$date->isPast() ? '-' : 'absent'])['status'];
                                
                                switch ($status) {
                                    case 'present': $presentCount++; break;
                                    case 'late': $lateCount++; break;
                                    case 'excused': $excusedCount++; break;
                                    case 'sick': $sickCount++; break;
                                    case 'absent': $absentCount++; break;
                                }
                            }
                        @endphp
                        <div class="grid grid-cols-3 gap-2 text-center text-xs">
                             <div class="bg-green-50 dark:bg-green-900/20 p-2 rounded-lg">
                                 <span class="block font-bold text-green-700 dark:text-green-400 text-lg">{{ $presentCount }}</span>
                                 <span class="text-green-600 dark:text-green-500">Hadir</span>
                             </div>
                             <div class="bg-amber-50 dark:bg-amber-900/20 p-2 rounded-lg">
                                 <span class="block font-bold text-amber-700 dark:text-amber-400 text-lg">{{ $lateCount }}</span>
                                 <span class="text-amber-600 dark:text-amber-500">Terlambat</span>
                             </div>
                             <div class="bg-red-50 dark:bg-red-900/20 p-2 rounded-lg">
                                 <span class="block font-bold text-red-700 dark:text-red-400 text-lg">{{ $absentCount }}</span>
                                 <span class="text-red-600 dark:text-red-500">Alpha</span>
                             </div>
                              <div class="bg-blue-50 dark:bg-blue-900/20 p-2 rounded-lg">
                                 <span class="block font-bold text-blue-700 dark:text-blue-400 text-lg">{{ $excusedCount }}</span>
                                 <span class="text-blue-600 dark:text-blue-500">Izin</span>
                             </div>
                              <div class="bg-gray-50 dark:bg-gray-700/30 p-2 rounded-lg">
                                 <span class="block font-bold text-gray-700 dark:text-gray-400 text-lg">{{ $sickCount }}</span>
                                 <span class="text-gray-600 dark:text-gray-500">Sakit</span>
                             </div>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Desktop Table View -->
    <div class="hidden sm:block overflow-x-scroll">
        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
                        {{ $showUserDetail ? __('Name') : __('Name') . '/' . __('Date') }}
                    </th>
                    @if ($showUserDetail)
                        <th scope="col"
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
                            {{ __('NIP') }}
                        </th>
                        <th scope="col"
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
                            {{ __('Division') }}
                        </th>
                        <th scope="col"
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
                            {{ __('Job Title') }}
                        </th>
                        @if ($isPerDayFilter)
                            <th scope="col"
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
                                {{ __('Shift') }}
                            </th>
                        @endif
                    @endif
                    @foreach ($dates as $date)
                        @php
                            if (!$isPerDayFilter && $date->isSunday()) {
                                // Minggu merah
                                $textClass = 'text-red-500 dark:text-red-300';
                            } elseif (!$isPerDayFilter && $date->isFriday()) {
                                // Jumat hijau
                                $textClass = 'text-green-500 dark:text-green-300';
                            } else {
                                $textClass = 'text-gray-500 dark:text-gray-300';
                            }
                        @endphp
                        <th scope="col"
                            class="{{ $textClass }} text-nowrap border border-gray-300 px-1 py-3 text-center text-xs font-medium dark:border-gray-600">
                            @if ($isPerDayFilter)
                                Status
                            @else
                                {{ $date->format('d/m') }}
                            @endif
                        </th>
                    @endforeach
                    @if ($isPerDayFilter)
                        <th scope="col"
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
                            {{ __('Time In') }}
                        </th>
                        <th scope="col"
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300">
                            {{ __('Time Out') }}
                        </th>
                    @endif
                    @if (!$isPerDayFilter)
                        @foreach (['H', 'T', 'I', 'S', 'A'] as $_st)
                            <th scope="col"
                                class="text-nowrap border border-gray-300 px-1 py-3 text-center text-xs font-medium text-gray-500 dark:border-gray-600 dark:text-gray-300">
                                {{ $_st }}
                            </th>
                        @endforeach
                    @endif
                    @if ($isPerDayFilter)
                        <th scope="col" class="relative">
                            <span class="sr-only">Actions</span>
                        </th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                @php
                    $class = 'cursor-pointer px-4 py-3 text-sm font-medium text-gray-900 dark:text-white';
                @endphp
                @foreach ($employees as $employee)
                    @php
                        $attendances = $employee->attendances;
                    @endphp
                    <tr wire:key="{{ $employee->id }}" class="group">
                        {{-- Detail karyawan --}}
                        <td
                            class="{{ $class }} text-nowrap group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                            {{ $employee->name }}
                        </td>
                        @if ($showUserDetail)
                            <td class="{{ $class }} group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                                {{ $employee->nip }}
                            </td>
                            <td
                                class="{{ $class }} text-nowrap group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                                {{ $employee->division?->name ?? '-' }}
                            </td>
                            <td
                                class="{{ $class }} text-nowrap group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                                {{ $employee->jobTitle?->name ?? '-' }}
                            </td>
                            @if ($isPerDayFilter)
                                @php
                                    $attendance = $employee->attendances->isEmpty()
                                        ? null
                                        : $employee->attendances->first();
                                    $timeIn = $attendance ? $attendance['time_in'] : null;
                                    $timeOut = $attendance ? $attendance['time_out'] : null;
                                @endphp
                                <td
                                    class="{{ $class }} text-nowrap group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                                    {{ $attendance['shift'] ?? '-' }}
                                </td>
                            @endif
                        @endif

                        {{-- Absensi --}}
                        @php
                            $presentCount = 0;
                            $lateCount = 0;
                            $excusedCount = 0;
                            $sickCount = 0;
                            $absentCount = 0;
                        @endphp
                        @foreach ($dates as $date)
                            @php
                                $isWeekend = $date->isWeekend();
                                $attendance = $attendances->firstWhere(
                                    fn($v, $k) => \Carbon\Carbon::parse($v['date'])->isSameDay($date),
                                );
                                $status = ($attendance ?? [
                                    'status' => $isWeekend || !$date->isPast() ? '-' : 'absent',
                                ])['status'];
                                switch ($status) {
                                    case 'present':
                                        $shortStatus = 'H';
                                        $bgColor =
                                            'bg-green-200 dark:bg-green-800 hover:bg-green-300 dark:hover:bg-green-700 border border-green-300 dark:border-green-600';
                                        $presentCount++;
                                        break;
                                    case 'late':
                                        $shortStatus = 'T';
                                        $bgColor =
                                            'bg-amber-200 dark:bg-amber-800 hover:bg-amber-300 dark:hover:bg-amber-700 border border-amber-300 dark:border-amber-600';
                                        $lateCount++;
                                        break;
                                    case 'excused':
                                        $shortStatus = 'I';
                                        $bgColor =
                                            'bg-blue-200 dark:bg-blue-800 hover:bg-blue-300 dark:hover:bg-blue-700 border border-blue-300 dark:border-blue-600';
                                        $excusedCount++;
                                        break;
                                    case 'sick':
                                        $shortStatus = 'S';
                                        $bgColor =
                                            'hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600';
                                        $sickCount++;
                                        break;
                                    case 'absent':
                                        $shortStatus = 'A';
                                        $bgColor =
                                            'bg-red-200 dark:bg-red-800 hover:bg-red-300 dark:hover:bg-red-700 border border-red-300 dark:border-red-600';
                                        $absentCount++;
                                        break;
                                    default:
                                        $shortStatus = '-';
                                        $bgColor =
                                            'hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600';
                                        break;
                                }
                            @endphp
                            @if (!$isPerDayFilter && $attendance && ($attendance['attachment'] || $attendance['note'] || $attendance['coordinates']))
                                <td
                                    class="{{ $bgColor }} cursor-pointer text-center text-sm font-medium text-gray-900 dark:text-white">
                                    <button class="w-full px-1 py-3" wire:click="show({{ $attendance['id'] }})">
                                        {{ $isPerDayFilter ? __($status) : $shortStatus }}
                                    </button>
                                </td>
                            @else
                                <td
                                    class="{{ $bgColor }} text-nowrap cursor-pointer px-1 py-3 text-center text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $isPerDayFilter ? __($status) : $shortStatus }}
                                </td>
                            @endif
                        @endforeach

                        {{-- Waktu masuk/keluar --}}
                        @if ($isPerDayFilter)
                            <td class="{{ $class }} group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                                {{ $timeIn ?? '-' }}
                            </td>
                            <td class="{{ $class }} group-hover:bg-gray-100 dark:group-hover:bg-gray-700">
                                {{ $timeOut ?? '-' }}
                            </td>
                        @endif

                        {{-- Total --}}
                        @if (!$isPerDayFilter)
                            @foreach ([$presentCount, $lateCount, $excusedCount, $sickCount, $absentCount] as $statusCount)
                                <td
                                    class="cursor-pointer border border-gray-300 px-1 py-3 text-center text-sm font-medium text-gray-900 group-hover:bg-gray-100 dark:border-gray-600 dark:text-white dark:group-hover:bg-gray-700">
                                    {{ $statusCount }}
                                </td>
                            @endforeach
                        @endif

                        {{-- Action --}}
                        @if ($isPerDayFilter)
                            @php
                                $attendance = $employee->attendances->isEmpty()
                                    ? null
                                    : $employee->attendances->first();
                            @endphp
                            <td
                                class="cursor-pointer text-center text-sm font-medium text-gray-900 group-hover:bg-gray-100 dark:text-white dark:group-hover:bg-gray-700">
                                <div class="flex items-center justify-center gap-3">
                                    @if ($attendance && ($attendance['attachment'] || $attendance['note'] || $attendance['coordinates']))
                                        <button type="button" 
                                            wire:click="show({{ $attendance->id }})"
                                            class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 dark:text-blue-300 dark:bg-blue-900/50 dark:hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            {{ __('Detail') }}
                                        </button>
                                    @else
                                        -
                                    @endif
                                </div>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($employees->isEmpty())
        <div class="my-2 text-center text-sm font-medium text-gray-900 dark:text-gray-100">
            Tidak ada data
        </div>
    @endif
    <div class="mt-3">
        {{ $employees->links() }}
    </div>

    <x-attendance-detail-modal :current-attendance="$currentAttendance" />
    @stack('attendance-detail-scripts')
</div>
