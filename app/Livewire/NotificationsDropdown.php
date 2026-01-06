<?php

namespace App\Livewire;

use Livewire\Component;

use Illuminate\Support\Facades\Auth;

class NotificationsDropdown extends Component
{
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        
        if (isset($notification->data['url'])) {
            return redirect($notification->data['url']);
        }
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    public function render()
    {
        return view('livewire.notifications-dropdown', [
            'notifications' => Auth::user()->unreadNotifications,
        ]);
    }
}
