<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\AttendanceLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_creates_attendance_log()
    {
        $employee = Employee::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'fingerprint_id' => '12345',
            'status' => 'active',
            'salary' => 5000,
        ]);

        $data = [
            'fingerprint_id' => '12345',
            'date' => '2023-10-01',
            'check_in' => '08:00',
            'check_out' => '17:00',
        ];

        $response = $this->postJson('/api/attendance-sync', $data);

        $response->assertStatus(200);
        $response->assertJson(['ok' => true]);

        // Check if record exists using count to avoid date format issues in SQLite
        $this->assertEquals(1, AttendanceLog::where('employee_id', $employee->id)->count());

        $log = AttendanceLog::where('employee_id', $employee->id)->first();
        $this->assertEquals('2023-10-01', $log->date->format('Y-m-d'));
        // Compare times as strings or objects
        $this->assertEquals('08:00:00', $log->check_in->format('H:i:s'));
    }

    public function test_batch_sync_creates_multiple_logs()
    {
        $emp1 = Employee::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'fingerprint_id' => '101',
            'status' => 'active',
            'salary' => 5000,
        ]);

        $emp2 = Employee::create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'fingerprint_id' => '102',
            'status' => 'active',
            'salary' => 5000,
        ]);

        $data = [
            [
                'fingerprint_id' => '101',
                'date' => '2023-10-02',
                'check_in' => '09:00',
            ],
            [
                'fingerprint_id' => '102',
                'date' => '2023-10-02',
                'check_in' => '08:55',
            ],
            [
                'fingerprint_id' => '999', // Unknown
                'date' => '2023-10-02',
                'check_in' => '08:00',
            ]
        ];

        $response = $this->postJson('/api/attendance-sync', $data);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => 2,
            'failed' => 1,
            'errors' => [
                ['index' => 2, 'error' => 'Employee not found']
            ]
        ]);

        $this->assertEquals(1, AttendanceLog::where('employee_id', $emp1->id)->whereDate('date', '2023-10-02')->count());
        $this->assertEquals(1, AttendanceLog::where('employee_id', $emp2->id)->whereDate('date', '2023-10-02')->count());
    }

    public function test_sync_fails_if_employee_not_found()
    {
        $data = [
            'fingerprint_id' => '99999',
            'date' => '2023-10-01',
        ];

        $response = $this->postJson('/api/attendance-sync', $data);

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Employee not found']);
    }
}
