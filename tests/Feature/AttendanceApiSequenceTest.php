<?php

namespace Tests\Feature;

use App\Models\AttendanceLog;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceApiSequenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('app.timezone', 'UTC');
    }

    public function test_dedup_ignores_scans_within_five_minutes(): void
    {
        $emp = Employee::create(['first_name' => 'A','last_name' => 'B','email' => 'a.b+1001@example.com','fingerprint_id' => 1001,'status' => 'active','salary' => 100]);
        $this->postJson('/api/attendance-sync', [
            'fingerprint_id' => 1001,
            'scan_time' => '2024-12-12T09:00:00.000Z',
        ])->assertStatus(200);
        $this->postJson('/api/attendance-sync', [
            'fingerprint_id' => 1001,
            'scan_time' => '2024-12-12T09:04:00.000Z',
        ])->assertStatus(200);

        $log = AttendanceLog::where('employee_id', $emp->id)->whereDate('date', '2024-12-12')->first();
        $this->assertNotNull($log);
        $this->assertSame('09:00', $log->check_in->format('H:i'));
        $this->assertNull($log->check_out);
    }

    public function test_sequence_in_out_same_day(): void
    {
        $emp = Employee::create(['first_name' => 'C','last_name' => 'D','email' => 'c.d+1002@example.com','fingerprint_id' => 1002,'status' => 'active','salary' => 100]);
        $this->postJson('/api/attendance-sync', [
            'fingerprint_id' => 1002,
            'scan_time' => '2024-12-12T09:00:00.000Z',
        ])->assertStatus(200);
        $this->postJson('/api/attendance-sync', [
            'fingerprint_id' => 1002,
            'scan_time' => '2024-12-12T18:00:00.000Z',
        ])->assertStatus(200);

        $log = AttendanceLog::where('employee_id', $emp->id)->whereDate('date', '2024-12-12')->first();
        $this->assertNotNull($log);
        $this->assertSame('09:00', $log->check_in->format('H:i'));
        $this->assertSame('18:00', $log->check_out->format('H:i'));
    }

    public function test_cross_midnight_checkout_updates_previous_day_log(): void
    {
        $emp = Employee::create(['first_name' => 'E','last_name' => 'F','email' => 'e.f+1003@example.com','fingerprint_id' => 1003,'status' => 'active','salary' => 100]);
        $this->postJson('/api/attendance-sync', [
            'fingerprint_id' => 1003,
            'scan_time' => '2024-12-12T23:00:00.000Z',
        ])->assertStatus(200);
        $this->postJson('/api/attendance-sync', [
            'fingerprint_id' => 1003,
            'scan_time' => '2024-12-13T07:00:00.000Z',
        ])->assertStatus(200);

        $prev = AttendanceLog::where('employee_id', $emp->id)->whereDate('date', '2024-12-12')->first();
        $nextCount = AttendanceLog::where('employee_id', $emp->id)->whereDate('date', '2024-12-13')->count();
        $this->assertNotNull($prev);
        $this->assertSame('23:00', $prev->check_in->format('H:i'));
        $this->assertSame('07:00', $prev->check_out->format('H:i'));
        $this->assertSame(0, $nextCount);
    }
}
