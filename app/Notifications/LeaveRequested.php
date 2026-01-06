<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequested extends Notification
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
        return [
            'type' => 'leave_request',
            'user_id' => $this->attendance->user_id,
            'user_name' => $this->attendance->user->name,
            'leave_type' => $this->attendance->status,
            'date' => $this->attendance->date->format('Y-m-d'),
            'message' => "New leave request from {$this->attendance->user->name}",
            'url' => route('admin.leaves'),
        ];
    }
}
