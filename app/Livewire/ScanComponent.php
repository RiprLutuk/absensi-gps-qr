<?php

namespace App\Livewire;

use App\ExtendedCarbon;
use App\Models\Attendance;
use App\Models\Barcode;
use App\Models\Shift;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Ballen\Distical\Calculator as DistanceCalculator;
use Ballen\Distical\Entities\LatLong;
use Illuminate\Support\Carbon;

class ScanComponent extends Component
{
    public ?Attendance $attendance = null;
    public $shift_id = null;
    public $shifts = null;
    public ?array $currentLiveCoords = null;
    public string $successMsg = '';
    public bool $isAbsence = false;

    public function scan(string $barcode, ?float $lat = null, ?float $lng = null)
    {
        // Update coordinates if provided
        if ($lat !== null && $lng !== null) {
            $this->currentLiveCoords = [$lat, $lng];
        }

        if (is_null($this->currentLiveCoords)) {
            return __('Invalid location');
        } else if (is_null($this->shift_id)) {
            return __('Invalid shift');
        }

        /** @var Attendance */
        $attendanceForDay = Attendance::where('user_id', Auth::user()->id)
            ->where('date', date('Y-m-d'))
            ->first();

        if ($attendanceForDay && in_array($attendanceForDay->status, ['sick', 'excused'])) {
            return __('Anda tidak dapat melakukan absensi karena sedang Cuti/Izin/Sakit.');
        }

        /** @var Barcode */
        $barcode = Barcode::firstWhere('value', $barcode);
        if (!Auth::check() || !$barcode) {
            return 'Invalid barcode';
        }

        $barcodeLocation = new LatLong($barcode->latLng['lat'], $barcode->latLng['lng']);
        $userLocation = new LatLong($this->currentLiveCoords[0], $this->currentLiveCoords[1]);

        if (($distance = $this->calculateDistance($userLocation, $barcodeLocation)) > $barcode->radius) {
            return __('Location out of range') . ": $distance" . "m. Max: $barcode->radius" . "m";
        }

        /** @var Attendance */
        $existingAttendance = Attendance::where('user_id', Auth::user()->id)
            ->where('date', date('Y-m-d'))
            ->where('barcode_id', $barcode->id)
            ->first();

        if (!$existingAttendance) {
            // Check In
            $attendance = $this->createAttendance($barcode);
            $this->successMsg = __('Attendance In Successful');
            \App\Models\ActivityLog::record('Check In', 'User checked in via barcode: ' . $barcode->name);
        } else {
            // Check Out
            $attendance = $existingAttendance;
            $attendance->update([
                'time_out' => date('H:i:s'),
                'latitude_out' => doubleval($this->currentLiveCoords[0]),
                'longitude_out' => doubleval($this->currentLiveCoords[1]),
            ]);
            $this->successMsg = __('Attendance Out Successful');
            \App\Models\ActivityLog::record('Check Out', 'User checked out.');
        }

        if ($attendance) {
            $this->setAttendance($attendance->fresh());
            Attendance::clearUserAttendanceCache(Auth::user(), Carbon::parse($attendance->date));
            return true;
        }
    }

    public function calculateDistance(LatLong $a, LatLong $b)
    {
        $distanceCalculator = new DistanceCalculator($a, $b);
        $distanceInMeter = floor($distanceCalculator->get()->asKilometres() * 1000); // convert to meters
        return $distanceInMeter;
    }

    /** @return Attendance */
    public function createAttendance(Barcode $barcode)
    {
        $now = Carbon::now();
        $date = $now->format('Y-m-d');
        $timeIn = $now->format('H:i:s');

        /** @var Shift */
        $shift = Shift::find($this->shift_id);
        $status = Carbon::now()->setTimeFromTimeString($shift->start_time)->lt($now) ? 'late' : 'present';

        return Attendance::create([
            'user_id' => Auth::user()->id,
            'barcode_id' => $barcode->id,
            'date' => $date,
            'time_in' => $timeIn,
            'time_out' => null,
            'shift_id' => $shift->id,

            // New: Separate location for check in
            'latitude_in' => doubleval($this->currentLiveCoords[0]),
            'longitude_in' => doubleval($this->currentLiveCoords[1]),

            // Legacy: Keep for backward compatibility (optional)
            'latitude' => doubleval($this->currentLiveCoords[0]),
            'longitude' => doubleval($this->currentLiveCoords[1]),

            'status' => $status,
            'note' => null,
            'attachment' => null,
        ]);
    }

    protected function setAttendance(Attendance $attendance)
    {
        $this->attendance = $attendance;
        $this->shift_id = $attendance->shift_id;
        $this->isAbsence = $attendance->status !== 'present' && $attendance->status !== 'late';
    }

    public function getAttendance()
    {
        if (is_null($this->attendance)) {
            return null;
        }
        return [
            'time_in' => $this->attendance?->time_in,
            'time_out' => $this->attendance?->time_out,
            'latitude_in' => $this->attendance?->latitude_in,
            'longitude_in' => $this->attendance?->longitude_in,
            'latitude_out' => $this->attendance?->latitude_out,
            'longitude_out' => $this->attendance?->longitude_out,
            'shift_end_time' => $this->attendance?->shift?->end_time,
        ];
    }

    public function mount()
    {
        $this->shifts = Shift::all();

        /** @var Attendance */
        $attendance = Attendance::where('user_id', Auth::user()->id)
            ->where('date', date('Y-m-d'))->first();

        if ($attendance) {
            $this->setAttendance($attendance);
        } else {
            // Priority 1: Check Manual Schedule
            /** @var \App\Models\Schedule */
            $schedule = \App\Models\Schedule::where('user_id', Auth::user()->id)
                ->where('date', date('Y-m-d'))
                ->first();

            if ($schedule && $schedule->shift_id) {
                // Use Scheduled Shift
                $this->shift_id = $schedule->shift_id;
            } else {
                // Priority 2: Auto-detect closest shift (Fallback)
                // get closest shift from current time
                $closest = ExtendedCarbon::now()
                    ->closestFromDateArray($this->shifts->pluck('start_time')->toArray());

                $this->shift_id = $this->shifts
                    ->where(fn(Shift $shift) => $shift->start_time == $closest->format('H:i:s'))
                    ->first()->id;
            }
        }
    }

    public function render()
    {
        return view('livewire.scan');
    }
}
