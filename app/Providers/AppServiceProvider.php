<?php

namespace App\Providers;

use App\Events\AttendanceUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
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
        Event::listen(AttendanceUpdated::class, function (AttendanceUpdated $e) {
            \Log::info('AttendanceUpdated', [
                'employee_id' => $e->log->employee_id,
                'date' => $e->log->date->toDateString(),
                'check_in' => $e->log->check_in,
                'check_out' => $e->log->check_out,
                'status' => $e->log->status,
                'device_status' => $e->deviceStatus,
            ]);
            $uid = optional(auth()->user())->id;
            if ($uid) {
                DB::table('activity_logs')->insert([
                    'user_id' => $uid,
                    'action' => 'attendance.updated',
                    'meta' => json_encode([
                        'employee_id' => $e->log->employee_id,
                        'date' => $e->log->date->toDateString(),
                        'check_in' => $e->log->check_in,
                        'check_out' => $e->log->check_out,
                        'status' => $e->log->status,
                        'shift_type' => $e->log->shift_type,
                        'expected_check_out' => $e->log->expected_check_out,
                        'overtime_minutes' => $e->log->overtime_minutes,
                        'early_arrival' => $e->log->early_arrival,
                        'late_checkin' => $e->log->late_checkin,
                        'missed_checkout' => $e->log->missed_checkout,
                    ]),
                    'ip' => request()->ip(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
