<?php

namespace App\Livewire\Admin;

use Livewire\Component;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class AnalyticsDashboard extends Component
{
    public $month;
    public $year;

    public function mount()
    {
        $this->month = date('m');
        $this->year = date('Y');
    }

    public function updated($property)
    {
        if (in_array($property, ['month', 'year'])) {
            $this->dispatch('chart-update', 
                trend: $this->attendanceTrend, 
                metrics: $this->attendanceMetrics
            );
        }
    }

    public function getAttendanceMetricsProperty()
    {
        return Attendance::whereMonth('date', $this->month)
            ->whereYear('date', $this->year)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }

    public function getAttendanceTrendProperty()
    {
        $startDate = Carbon::createFromDate($this->year, $this->month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $data = Attendance::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->select('date', 'status', DB::raw('count(*) as total'))
            ->groupBy('date', 'status')
            ->get();

        $trend = [];
        $current = $startDate->copy();
        
        while ($current <= $endDate && $current <= now()) {
            $dateStr = $current->format('Y-m-d');
            $dayData = $data->where('date', $dateStr);
            
            $trend['labels'][] = $current->format('d M');
            $trend['present'][] = $dayData->where('status', 'present')->sum('total');
            $trend['late'][] = $dayData->where('status', 'late')->sum('total');
            $trend['absent'][] = $dayData->whereIn('status', ['sick', 'excused', 'alpha'])->sum('total');
            
            $current->addDay();
        }

        return $trend;
    }

    public function getTopDiligentEmployeesProperty()
    {
        // Logic: Lowest average check-in time (for 'present' status only)
        return User::join('attendances', 'users.id', '=', 'attendances.user_id')
            ->whereMonth('attendances.date', $this->month)
            ->whereYear('attendances.date', $this->year)
            ->where('attendances.status', 'present')
            ->whereNotNull('attendances.time_in')
            ->select('users.id', 'users.name', 'users.profile_photo_path', DB::raw('AVG(TIME_TO_SEC(attendances.time_in)) as avg_check_in'))
            ->groupBy('users.id', 'users.name', 'users.profile_photo_path')
            ->orderBy('avg_check_in', 'asc')
            ->limit(5)
            ->get();
    }

    public function getTopLateEmployeesProperty()
    {
        // Logic: Highest count of 'late' status
        return User::join('attendances', 'users.id', '=', 'attendances.user_id')
            ->whereMonth('attendances.date', $this->month)
            ->whereYear('attendances.date', $this->year)
            ->where('attendances.status', 'late')
            ->select('users.id', 'users.name', 'users.profile_photo_path', DB::raw('count(*) as late_count'))
            ->groupBy('users.id', 'users.name', 'users.profile_photo_path')
            ->orderByDesc('late_count')
            ->limit(5)
            ->get();
    }

    public function getTopEarlyLeaversProperty()
    {
        // Logic: Count of check-outs before shift end time
        return User::join('attendances', 'users.id', '=', 'attendances.user_id')
            ->join('shifts', 'attendances.shift_id', '=', 'shifts.id')
            ->whereMonth('attendances.date', $this->month)
            ->whereYear('attendances.date', $this->year)
            ->whereIn('attendances.status', ['present', 'late'])
            ->whereNotNull('attendances.time_out')
            ->whereRaw('attendances.time_out < shifts.end_time')
            ->select('users.id', 'users.name', 'users.profile_photo_path', DB::raw('count(*) as early_leave_count'))
            ->groupBy('users.id', 'users.name', 'users.profile_photo_path')
            ->orderByDesc('early_leave_count')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.analytics-dashboard', [
            'metrics' => $this->attendanceMetrics,
            'trend' => $this->attendanceTrend,
            'topDiligent' => $this->topDiligentEmployees,
            'topLate' => $this->topLateEmployees,
            'topEarlyLeavers' => $this->topEarlyLeavers,
        ]);
    }
}
