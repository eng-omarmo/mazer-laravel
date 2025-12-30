<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AttendanceApiController extends Controller
{
    public function sync(Request $request)
    {
        $validated = $request->validate([
            'fingerprint_id' => ['required', 'string'],
            'date' => ['required', 'date'],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i'],
        ]);
        $employee = Employee::where('fingerprint_id', $validated['fingerprint_id'])->first();
        if (! $employee) {
            Log::warning('Attendance sync: fingerprint not mapped', $validated);
            return response()->json(['error' => 'Employee not found'], 404);
        }
        $status = 'present';
        if (empty($validated['check_in'])) {
            $status = 'absent';
        } else {
            if (strtotime($validated['check_in']) > strtotime('09:15')) {
                $status = 'late';
            }
            if (! empty($validated['check_out']) && strtotime($validated['check_out']) < strtotime('17:00')) {
                $status = 'early_leave';
            }
        }
        $existing = AttendanceLog::where('employee_id', $employee->id)->where('date', $validated['date'])->first();
        if ($existing) {
            $existing->update([
                'check_in' => $validated['check_in'] ?? $existing->check_in,
                'check_out' => $validated['check_out'] ?? $existing->check_out,
                'status' => $status,
                'source' => 'device',
            ]);
        } else {
            AttendanceLog::create([
                'employee_id' => $employee->id,
                'date' => $validated['date'],
                'check_in' => $validated['check_in'] ?? null,
                'check_out' => $validated['check_out'] ?? null,
                'status' => $status,
                'source' => 'device',
            ]);
        }

        return response()->json(['ok' => true]);
    }
}
