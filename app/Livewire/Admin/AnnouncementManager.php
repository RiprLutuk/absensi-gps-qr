<?php

namespace App\Livewire\Admin;

use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class AnnouncementManager extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editMode = false;
    public $announcementId = null;
    
    public $title = '';
    public $content = '';
    public $priority = 'normal';
    public $publish_date = '';
    public $expire_date = '';
    public $is_active = true;

    protected $rules = [
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'priority' => 'required|in:low,normal,high',
        'publish_date' => 'required|date',
        'expire_date' => 'nullable|date|after_or_equal:publish_date',
        'is_active' => 'boolean',
    ];

    public function create()
    {
        $this->reset(['announcementId', 'title', 'content', 'priority', 'publish_date', 'expire_date']);
        $this->priority = 'normal';
        $this->is_active = true;
        $this->publish_date = now()->format('Y-m-d');
        $this->editMode = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $announcement = Announcement::findOrFail($id);
        $this->announcementId = $announcement->id;
        $this->title = $announcement->title;
        $this->content = $announcement->content;
        $this->priority = $announcement->priority;
        $this->publish_date = $announcement->publish_date->format('Y-m-d');
        $this->expire_date = $announcement->expire_date?->format('Y-m-d');
        $this->is_active = $announcement->is_active;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'content' => $this->content,
            'priority' => $this->priority,
            'publish_date' => $this->publish_date,
            'expire_date' => $this->expire_date ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->editMode) {
            Announcement::find($this->announcementId)->update($data);
            session()->flash('success', __('Announcement updated successfully.'));
        } else {
            $data['created_by'] = Auth::id();
            Announcement::create($data);
            session()->flash('success', __('Announcement created successfully.'));
        }

        $this->showModal = false;
    }

    public function delete($id)
    {
        Announcement::destroy($id);
        session()->flash('success', __('Announcement deleted successfully.'));
    }

    public function toggleActive($id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->update(['is_active' => !$announcement->is_active]);
    }

    public function render()
    {
        return view('livewire.admin.announcement-manager', [
            'announcements' => Announcement::with('creator')
                ->orderBy('publish_date', 'desc')
                ->paginate(10),
        ]);
    }
}
