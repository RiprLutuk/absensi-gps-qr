<?php

namespace App\Exports;

use App\Models\ActivityLog;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ActivityLogsExport implements FromView
{
    public function view(): View
    {
        return view('admin.import-export.export-activity-logs', [
            'logs' => ActivityLog::with('user')->latest()->get()
        ]);
    }
}
