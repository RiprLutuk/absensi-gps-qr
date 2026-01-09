<?php

namespace App\Livewire;

use App\Livewire\Traits\AttendanceDetailTrait;
use App\Models\Attendance;
use App\Models\Holiday;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class AttendanceHistoryComponent extends Component
{
    use AttendanceDetailTrait;

    public ?string $month;
    public $selectedYear;
    public $selectedMonth;

    public function mount()
    {
        $this->selectedYear = date('Y');
        $this->selectedMonth = date('m');
        $this->month = "{$this->selectedYear}-{$this->selectedMonth}";
    }

    public function updatedSelectedYear()
    {
        $this->updateMonth();
    }

    public function updatedSelectedMonth()
    {
        $this->updateMonth();
    }

    public function updateMonth()
    {
        $this->month = "{$this->selectedYear}-{$this->selectedMonth}";
    }

    public function render()
    {
        $user = auth()->user();
        
        try {
            $date = Carbon::parse($this->month);
        } catch (\Exception $e) {
            // Fallback calculation date only, do NOT reset user input
            $date = now();
        }

        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        
        // Start from the beginning of the week (Sunday)
        $startGrid = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        // End at the end of the week (Saturday)
        $endGrid = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);
        
        $dates = [];
        $current = $startGrid->copy();
        
        while ($current <= $endGrid) {
            $dates[] = $current->copy();
            $current->addDay();
        }

        $cached = Cache::remember(
            "attendance-$user->id-$date->month-$date->year",
            now()->addDay(),
            function () use ($user) {
                return Attendance::filter(
                    month: $this->month,
                    userId: $user->id,
                )->get(['id', 'status', 'date', 'latitude_in', 'longitude_in', 'latitude_out', 'longitude_out', 'attachment', 'note'])->toArray();
            }
        ) ?? [];

        $attendances = Attendance::hydrate($cached);
        
        // Calculate Counts
        $presentCount = $attendances->where('status', 'present')->count();
        $lateCount = $attendances->where('status', 'late')->count();
        $excusedCount = $attendances->where('status', 'excused')->count();
        $sickCount = $attendances->where('status', 'sick')->count();
        $absentCount = $attendances->where('status', 'absent')->count();

        // Map additional attributes...
        $attendances->transform(function (Attendance $v) {
            $v->setAttribute('coordinates', $v->lat_lng);
             return $v;
        });
        
        $attendanceToday = $attendances->firstWhere(fn($v, $_) => $v['date'] === Carbon::now()->format('Y-m-d'));
        
        // Get holidays for this month (including recurring)
        $holidays = Holiday::where(function ($query) use ($startOfMonth, $endOfMonth) {
            $query->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->orWhere(function ($q) use ($startOfMonth, $endOfMonth) {
                    $q->where('is_recurring', true)
                        ->whereRaw('MONTH(date) = ?', [$startOfMonth->month]);
                });
        })->get()->keyBy(function ($holiday) use ($startOfMonth) {
            // For recurring holidays, use current year's date as key
            if ($holiday->is_recurring) {
                return $startOfMonth->year . '-' . $holiday->date->format('m-d');
            }
            return $holiday->date->format('Y-m-d');
        });
        
        return view('livewire.attendance-history', [
            'attendances' => $attendances,
            'attendanceToday' => $attendanceToday,
            'dates' => $dates,
            'currentMonth' => $date->month,
            'holidays' => $holidays,
            'counts' => [
                'present' => $presentCount,
                'late' => $lateCount,
                'excused' => $excusedCount,
                'sick' => $sickCount,
                'absent' => $absentCount,
            ]
        ]);
    }
}
