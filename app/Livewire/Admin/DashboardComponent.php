<?php

namespace App\Livewire\Admin;

use App\Livewire\Traits\AttendanceDetailTrait;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class DashboardComponent extends Component
{
    use AttendanceDetailTrait;

    public $showStatModal = false;
    public $selectedStatType = '';
    public $detailList = [];

    public function showStatDetail($type)
    {
        $this->selectedStatType = $type;
        $this->showStatModal = true;
        $today = date('Y-m-d');

        if ($type === 'absent') {
             // Users who have NO attendance record for today (and are users, not admins)
             $this->detailList = User::where('group', 'user')
                ->whereDoesntHave('attendances', fn($q) => $q->where('date', $today))
                ->get();
        } else {
            $query = Attendance::with(['user', 'shift'])->where('date', $today);

            if ($type === 'early_checkout') {
                 $this->detailList = $query->get()->filter(function ($attendance) {
                    if (!$attendance->time_out || !$attendance->shift) return false;
                    return $attendance->time_out->format('H:i:s') < $attendance->shift->end_time;
                });
            } else {
                // present, late, excused, sick
                $this->detailList = $query->where('status', $type)->get();
            }
        }
    }

    public function closeStatModal()
    {
        $this->showStatModal = false;
        $this->detailList = [];
    }

    public function render()
    {
        /** @var Collection<Attendance>  */
        $attendances = Attendance::with('shift')->where('date', date('Y-m-d'))->get();

        /** @var Collection<User>  */
        $employees = User::where('group', 'user')
            ->paginate(20)
            ->through(function (User $user) use ($attendances) {
                return $user->setAttribute(
                    'attendance',
                    $attendances
                        ->where(fn (Attendance $attendance) => $attendance->user_id === $user->id)
                        ->first(),
                );
            });

        $employeesCount = User::where('group', 'user')->count();
        $presentCount = $attendances->where(fn ($attendance) => $attendance->status === 'present')->count();
        $lateCount = $attendances->where(fn ($attendance) => $attendance->status === 'late')->count();
        $excusedCount = $attendances->where(fn ($attendance) => $attendance->status === 'excused')->count();
        $sickCount = $attendances->where(fn ($attendance) => $attendance->status === 'sick')->count();
        $absentCount = $employeesCount - ($presentCount + $lateCount + $excusedCount + $sickCount);

        // Early Checkout Calculation
        $earlyCheckoutCount = $attendances->filter(function ($attendance) {
            if (!$attendance->time_out || !$attendance->shift) return false;
            // time_out is Carbon, shift->end_time is String 'H:i:s'
            return $attendance->time_out->format('H:i:s') < $attendance->shift->end_time;
        })->count();

        // Activity Logs (Optimized Storage)
        $recentLogs = \App\Models\ActivityLog::with('user')
            ->latest('updated_at')
            ->take(10)
            ->get();

        // Weekly Chart Data
        $startDate = now()->subDays(6);
        $endDate = now();
        $weeklyAttendances = Attendance::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])->get();
        
        $chartLabels = [];
        $chartPresent = [];
        $chartLate = [];
        $chartAbsent = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateString = $date->format('Y-m-d');
            $chartLabels[] = $date->format('d M');
            
            $dayAttendances = $weeklyAttendances->where('date', '>=', $date->startOfDay())->where('date', '<=', $date->endOfDay());
            $chartPresent[] = $dayAttendances->where('status', 'present')->count();
            $chartLate[] = $dayAttendances->where('status', 'late')->count();
            // Absent calculation is tricky without daily history snapshot, so we'll estimate or just show known statuses
            // For now, let's just show Present + Late vs Sick/Excused
            $chartAbsent[] = $dayAttendances->whereIn('status', ['sick', 'excused'])->count();
        }

        // Users checked in but not checked out (Overdue)
        // Includes today (if shift ended) and previous days
        $overdueUsers = Attendance::with(['user', 'shift'])
            ->whereNotNull('time_in')
            ->whereNull('time_out')
            ->orderByDesc('date')
            ->take(10) // Limit to prevent overflow
            ->get()
            ->filter(function ($attendance) {
                if (!$attendance->shift) return false;
                
                // If date is before today, it's definitely overdue
                if ($attendance->date < now()->format('Y-m-d')) {
                    return true;
                }
                
                // If date is today, check if current time > shift end time
                if ($attendance->date === now()->format('Y-m-d')) {
                    return now()->format('H:i:s') > $attendance->shift->end_time;
                }
                
                return false;
            });

        // Calendar Data: Leaves in current month
        $calendarLeaves = Attendance::with('user')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->whereIn('status', ['sick', 'excused'])
            ->get()
            ->map(fn($a) => [
                'title' => $a->user->name . ' (' . ucfirst($a->status) . ')',
                'start' => $a->date,
                'status' => $a->status
            ]);

        return view('livewire.admin.dashboard', [
            'employees' => $employees,
            'employeesCount' => $employeesCount,
            'presentCount' => $presentCount,
            'lateCount' => $lateCount,
            'earlyCheckoutCount' => $earlyCheckoutCount,
            'excusedCount' => $excusedCount,
            'sickCount' => $sickCount,
            'absentCount' => $absentCount,
            'recentLogs' => $recentLogs,
            'chartData' => [
                'labels' => $chartLabels,
                'present' => $chartPresent,
                'late' => $chartLate,
                'other' => $chartAbsent
            ],
            'overdueUsers' => $overdueUsers,
            'calendarLeaves' => $calendarLeaves,
        ]);
    }

    public function notifyUser($attendanceId)
    {
        $attendance = Attendance::find($attendanceId);
        if ($attendance && $attendance->user && $attendance->user->email) {
            \Illuminate\Support\Facades\Mail::to($attendance->user->email)->send(new \App\Mail\CheckoutReminderMail($attendance->user));
            
            // Log it
            \App\Models\ActivityLog::record('Notification Sent', 'Sent checkout reminder to ' . $attendance->user->name);
        }
    }
}
