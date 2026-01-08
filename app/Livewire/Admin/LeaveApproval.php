<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Notifications\LeaveStatusUpdated;
use Illuminate\Support\Facades\Notification;

#[Layout('layouts.app')]
class LeaveApproval extends Component
{
    use WithPagination;

    public $rejectionNote;
    public $selectedIds = [];
    public $confirmingRejection = false;

    public function render()
    {
        // Fetch all pending requests
        $allLeaves = Attendance::pending()
            ->with(['user.division']) // Eager load
            ->orderBy('date', 'asc')
            ->get();

        // Group by User ID, Status, and Note to combine related requests
        $groupedLeaves = $allLeaves->groupBy(function ($item) {
            return $item->user_id . '|' . $item->status . '|' . trim($item->note);
        });

        return view('livewire.admin.leave-approval', [
            'groupedLeaves' => $groupedLeaves
        ]);
    }

    public function approve($ids)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        Attendance::whereIn('id', $ids)->update([
            'approval_status' => Attendance::STATUS_APPROVED,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        $attendances = Attendance::whereIn('id', $ids)->get();
        foreach ($attendances as $attendance) {
            $attendance->user->notify(new LeaveStatusUpdated($attendance));
        }

        $this->dispatch('saved');
    }

    public function confirmReject($ids)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $this->selectedIds = $ids;
        $this->confirmingRejection = true;
    }

    public function reject()
    {
        Attendance::whereIn('id', $this->selectedIds)->update([
            'approval_status' => Attendance::STATUS_REJECTED,
            'status' => 'rejected', // Revert: Set explicit rejected status
            'rejection_note' => $this->rejectionNote,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        $attendances = Attendance::whereIn('id', $this->selectedIds)->get();
        foreach ($attendances as $attendance) {
            $attendance->user->notify(new LeaveStatusUpdated($attendance));
        }

        $this->confirmingRejection = false;
        $this->rejectionNote = '';
        $this->selectedIds = [];
        $this->dispatch('saved');
    }
}
