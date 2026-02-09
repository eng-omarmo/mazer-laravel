<?php

namespace Tests\Feature;

use App\Events\AttendanceUpdated;
use App\Models\AttendanceLog;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AttendanceSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_in_then_out_updates_consistently_and_dispatches_event(): void
    {
        $emp = Employee::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'salary' => 0,
            'fingerprint_id' => 'FP001',
        ]);

        Event::fake([AttendanceUpdated::class]);

        $date = now()->toDateString();

        $start = microtime(true);

        $this->postJson('/api/attendance-sync', [
            'fingerprint_id' => 'FP001',
            'date' => $date,
            'check_in' => '09:00',
        ])->assertOk();

        $this->postJson('/api/attendance-sync', [
            'fingerprint_id' => 'FP001',
            'date' => $date,
            'check_out' => '18:00',
        ])->assertOk();

        $elapsedMs = (microtime(true) - $start) * 1000;

        $log = AttendanceLog::where('employee_id', $emp->id)->whereDate('date', $date)->first();

        $this->assertNotNull($log);
        $this->assertSame('09:00', $log->check_in->format('H:i'));
        $this->assertSame('18:00', $log->check_out->format('H:i'));
        $this->assertSame('present', $log->status);

        Event::assertDispatched(AttendanceUpdated::class, 2);

        $this->assertTrue($elapsedMs < 100);
    }

    public function test_out_of_order_punches_resolve_to_earliest_in_latest_out(): void
    {
        $emp = Employee::create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'salary' => 0,
            'fingerprint_id' => 'FP002',
        ]);

        $date = now()->toDateString();

        $this->postJson('/api/attendance-sync', [
            'fingerprint_id' => 'FP002',
            'date' => $date,
            'check_in' => '09:30',
        ])->assertOk();

        $this->postJson('/api/attendance-sync', [
            'fingerprint_id' => 'FP002',
            'date' => $date,
            'check_out' => '17:30',
        ])->assertOk();

        $this->postJson('/api/attendance-sync', [
            'fingerprint_id' => 'FP002',
            'date' => $date,
            'check_in' => '08:45',
        ])->assertOk();

        $log = AttendanceLog::where('employee_id', $emp->id)->whereDate('date', $date)->first();
        $this->assertSame('08:45', $log->check_in->format('H:i'));
        $this->assertSame('17:30', $log->check_out->format('H:i'));
    }
}
