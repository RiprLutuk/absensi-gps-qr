<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            \App\Models\ActivityLog::create([
                'user_id' => $event->user->id,
                'action' => 'Login Successful',
                'description' => 'User logged in.',
                'ip_address' => request()->ip(),
            ]);
        });

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Failed::class, function ($event) {
            // Failed login usually doesn't have a user instance if user not found, but might have credentials
            // We'll try to find user by email if possible, or just log generic
             \App\Models\ActivityLog::create([
                'user_id' => null, // Can't link to user if failed
                'action' => 'Login Failed',
                'description' => 'Failed login attempt for email: ' . ($event->credentials['email'] ?? 'unknown'),
                'ip_address' => request()->ip(),
            ]);
        });
    }
}
