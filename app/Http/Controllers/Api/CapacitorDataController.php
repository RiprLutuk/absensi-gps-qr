<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CapacitorDataController extends Controller
{
   /**
    * Get current location from device
    * POST /api/device/location
    */
   public function getLocation(Request $request)
   {
      $validated = $request->validate([
         'latitude' => ['required', 'numeric'],
         'longitude' => ['required', 'numeric'],
         'accuracy' => ['nullable', 'numeric'],
      ]);

      try {
         return response()->json([
            'success' => true,
            'data' => [
               'latitude' => $validated['latitude'],
               'longitude' => $validated['longitude'],
               'accuracy' => $validated['accuracy'] ?? null,
               'timestamp' => now()->toIso8601String(),
            ]
         ]);
      } catch (\Exception $e) {
         return response()->json([
            'success' => false,
            'message' => 'Failed to process location data: ' . $e->getMessage()
         ], 422);
      }
   }

   /**
    * Save barcode scan result
    * POST /api/device/barcode
    */
   public function saveBarcodeData(Request $request)
   {
      $validated = $request->validate([
         'barcode_data' => ['required', 'string'],
         'latitude' => ['nullable', 'numeric'],
         'longitude' => ['nullable', 'numeric'],
         'timestamp' => ['nullable', 'date_format:Y-m-d H:i:s'],
      ]);

      try {
         $attendance = Attendance::updateOrCreate(
            [
               'user_id' => Auth::id(),
               'date' => now()->format('Y-m-d'),
            ],
            [
               'latitude' => $validated['latitude'] ?? null,
               'longitude' => $validated['longitude'] ?? null,
               'barcode_data' => $validated['barcode_data'],
               'check_in_time' => $validated['timestamp'] ?? now(),
            ]
         );

         return response()->json([
            'success' => true,
            'message' => 'Barcode data saved successfully',
            'attendance_id' => $attendance->id,
         ]);
      } catch (\Exception $e) {
         return response()->json([
            'success' => false,
            'message' => 'Failed to save barcode data: ' . $e->getMessage()
         ], 422);
      }
   }

   /**
    * Upload camera photo
    * POST /api/device/photo
    */
   public function uploadPhoto(Request $request)
   {
      $validated = $request->validate([
         'photo' => ['required', 'image', 'max:5120'], // 5MB
         'latitude' => ['nullable', 'numeric'],
         'longitude' => ['nullable', 'numeric'],
      ]);

      try {
         $path = $request->file('photo')->storePublicly(
            'attendance-photos',
            ['disk' => config('filesystems.default', 'public')]
         );

         $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', now()->format('Y-m-d'))
            ->first();

         if ($attendance) {
            $attendance->update([
               'photo' => $path,
               'latitude' => $validated['latitude'] ?? $attendance->latitude,
               'longitude' => $validated['longitude'] ?? $attendance->longitude,
            ]);
         } else {
            // Create new attendance record with photo if doesn't exist
            $attendance = Attendance::create([
               'user_id' => Auth::id(),
               'date' => now()->format('Y-m-d'),
               'photo' => $path,
               'latitude' => $validated['latitude'] ?? null,
               'longitude' => $validated['longitude'] ?? null,
               'status' => 'present',
            ]);
         }

         return response()->json([
            'success' => true,
            'message' => 'Photo uploaded successfully',
            'path' => Storage::url($path),
            'attendance_id' => $attendance->id,
         ]);
      } catch (\Exception $e) {
         return response()->json([
            'success' => false,
            'message' => 'Failed to upload photo: ' . $e->getMessage()
         ], 422);
      }
   }
   /**
    * Request device permissions status
    * GET /api/device/permissions
    */
   public function getPermissionsStatus(Request $request)
   {
      return response()->json([
         'success' => true,
         'permissions' => [
            'camera' => [
               'state' => 'prompt', // 'prompt', 'granted', 'denied'
               'description' => 'Camera access for barcode scanning'
            ],
            'geolocation' => [
               'state' => 'prompt',
               'description' => 'Location access for attendance tracking'
            ]
         ]
      ]);
   }
}
