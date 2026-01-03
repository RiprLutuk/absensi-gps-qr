<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'action', 'description', 'ip_address', 'count'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record($action, $description = null)
    {
        $userId = auth()->id();
        $ip = request()->ip();

        // Check for recent similar log (e.g., within last 1 hour)
        $recentLog = self::where('user_id', $userId)
            ->where('action', $action)
            ->where('description', $description)
            ->where('created_at', '>=', now()->subHour())
            ->latest()
            ->first();

        if ($recentLog) {
            $recentLog->increment('count');
            $recentLog->touch(); // Update updated_at
            return $recentLog;
        }

        return self::create([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $ip,
            'count' => 1,
        ]);
    }
}
