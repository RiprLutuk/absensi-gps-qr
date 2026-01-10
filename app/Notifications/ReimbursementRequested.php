<?php

namespace App\Notifications;

use App\Models\Reimbursement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReimbursementRequested extends Notification implements ShouldQueue
{
    use Queueable;

    public $reimbursement;

    public function __construct(Reimbursement $reimbursement)
    {
        $this->reimbursement = $reimbursement;
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];
        
        // Add mail if admin email is configured
        $adminEmail = \App\Models\Setting::getValue('notif.admin_email');
        if (!empty($adminEmail) && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $channels[] = 'mail';
        }
        
        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $userName = $this->reimbursement->user->name ?? 'Unknown';
        $amount = number_format($this->reimbursement->amount, 0, ',', '.');
        
        // Get app name from settings
        $appName = \App\Models\Setting::getValue('app.name', config('app.name', 'PasPapan'));
        
        return (new MailMessage)
            ->from(config('mail.from.address'), \App\Models\Setting::getValue('mail.from_name', $appName))
            ->replyTo(
                \App\Models\Setting::getValue('mail.reply_to_address', config('mail.from.address')),
                \App\Models\Setting::getValue('mail.reply_to_name', $appName)
            )
            ->subject('Sistem' . " - " . __('New Reimbursement Request') . ": $userName")
            ->greeting("Hello, Admin!")
            ->line("User **{$userName}** has submitted a new reimbursement request.")
            ->line("Type: **{$this->reimbursement->type}**")
            ->line("Amount: **Rp {$amount}**")
            ->line("Description: {$this->reimbursement->description}")
            ->action('Review Request', route('admin.reimbursements'))
            ->line('Please review this request at your earliest convenience.');
    }

    public function toArray(object $notifiable): array
    {
        $amount = number_format($this->reimbursement->amount, 0, ',', '.');
        
        return [
            'type' => 'reimbursement_request',
            'title' => 'New Reimbursement Request',
            'user_id' => $this->reimbursement->user_id,
            'user_name' => $this->reimbursement->user->name,
            'amount' => $amount,
            'message' => "Request from {$this->reimbursement->user->name}: {$this->reimbursement->type} (Rp {$amount})",
            'url' => route('admin.reimbursements'),
        ];
    }
}
