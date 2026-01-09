<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Component;

class BirthdayWidget extends Component
{
    public function render()
    {
        $today = Carbon::today();
        $endDate = $today->copy()->addDays(7);
        
        // Get users with birthdays in the next 7 days
        $upcomingBirthdays = User::whereNotNull('birth_date')
            ->whereRaw('DAYOFYEAR(birth_date) BETWEEN DAYOFYEAR(?) AND DAYOFYEAR(?)', [
                $today->format('Y-m-d'),
                $endDate->format('Y-m-d')
            ])
            ->orWhereRaw('DAYOFYEAR(birth_date) BETWEEN 1 AND DAYOFYEAR(?) AND DAYOFYEAR(?) > 358', [
                $endDate->format('Y-m-d'),
                $today->format('Y-m-d')
            ]) // Handle year wrap-around (late December)
            ->orderByRaw('DAYOFYEAR(birth_date)')
            ->limit(5)
            ->get()
            ->map(function ($user) use ($today) {
                $birthday = Carbon::parse($user->birth_date)->setYear($today->year);
                if ($birthday->isPast() && !$birthday->isToday()) {
                    $birthday->addYear();
                }
                $user->next_birthday = $birthday;
                $user->days_until = $today->diffInDays($birthday, false);
                return $user;
            })
            ->sortBy('days_until');

        return view('livewire.birthday-widget', [
            'birthdays' => $upcomingBirthdays,
        ]);
    }
}
