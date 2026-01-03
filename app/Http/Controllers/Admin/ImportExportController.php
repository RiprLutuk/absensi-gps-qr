<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;
use App\Exports\AttendancesExport;
use App\Exports\ActivityLogsExport;
use App\Models\Division;
use App\Models\JobTitle;
use App\Models\Education;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\Attendance;

class ImportExportController extends Controller
{
    public function users()
    {
        return view('admin.import-export.users');
    }

    public function attendances()
    {
        return view('admin.import-export.attendances');
    }

    public function exportUsers(Request $request)
    {
        $groups = $request->input('groups', ['user']); 
        if (is_string($groups)) {
            $groups = explode(',', $groups);
        }

        return Excel::download(
            new UsersExport($groups),
            'users.xlsx'
        );
    }

    public function exportAttendances(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $division = $request->input('division');
        $job_title = $request->input('job_title');
        $education = $request->input('education');

        $divName = $division ? Division::find($division)?->name : null;
        $jobName = $job_title ? JobTitle::find($job_title)?->name : null;
        $eduName = $education ? Education::find($education)?->name : null;

        $filename = 'attendances' 
            . ($month ? '_' . Carbon::parse($month)->format('F-Y') : '') 
            . ($year && !$month ? '_' . $year : '') 
            . ($divName ? '_' . Str::slug($divName) : '') 
            . ($jobName ? '_' . Str::slug($jobName) : '') 
            . ($eduName ? '_' . Str::slug($eduName) : '') 
            . '.xlsx';

        return Excel::download(new AttendancesExport(
            $month,
            $year,
            $division,
            $job_title,
            $education
        ), $filename);
    }

    public function exportActivityLogs()
    {
        return Excel::download(new ActivityLogsExport, 'activity-logs-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportReportPdf(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $date = Carbon::createFromDate($year, $month, 1);
        
        $attendances = Attendance::with('user', 'shift')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date')
            ->get()
            ->groupBy('user_id');
            
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.monthly_pdf', [
            'attendances' => $attendances,
            'month' => $date->format('F'),
            'year' => $year,
            'date' => $date
        ])->setPaper('a4', 'landscape');

        return $pdf->download('monthly-report-' . $date->format('F-Y') . '.pdf');
    }

    public function importUsers(Request $request)
    {
        abort(404);
    }

    public function importAttendances(Request $request)
    {
        abort(404);
    }
}
