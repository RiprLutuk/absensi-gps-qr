<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Settings extends Component
{
    public function mount()
    {
        if (!auth()->check() || !auth()->user()->isAdmin) {
             abort(403, 'Unauthorized action.');
        }
    }

    public function updateValue($id, $value)
    {
        if (!auth()->user()->isSuperadmin) {
            return; // Silently fail or abort
        }

        $setting = Setting::find($id);
        
        if ($setting) {
            $setting->update(['value' => $value]);
            Cache::forget("setting.{$setting->key}");
            
            $this->dispatch('saved'); // For sweetalert or notification
        }
    }

    public function render()
    {
        $groups = Setting::all()->groupBy('group');
        return view('livewire.admin.settings', ['groups' => $groups]);
    }
}
