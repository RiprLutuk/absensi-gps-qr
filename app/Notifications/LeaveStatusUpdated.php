<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveStatusUpdated extends Notification
{
    use Queueable;

    public $attendance;

    public function __construct($attendance)
    {
        $this->attendance = $attendance;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $status = ucfirst($this->attendance->approval_status);
        $emoji = $this->attendance->approval_status === 'approved' ? '✅' : '❌';
        
        return [
            'type' => 'leave_status',
            'attendance_id' => $this->attendance->id,
            'status' => $this->attendance->approval_status,
            'date' => $this->attendance->date->format('Y-m-d'),
            'message' => "Your leave for {$this->attendance->date->format('d M')} has been {$status} {$emoji}",
            'url' => route('attendance-history'),
        ];
    }
}
