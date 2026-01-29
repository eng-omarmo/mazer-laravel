<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AttendanceApiController extends Controller
{
    public function sync(Request $request)
    {
        $data = $request->all();

        // Handle batch sync (array of objects)
        if (isset($data[0]) && is_array($data[0])) {

            $results = ['success' => 0, 'failed' => 0, 'errors' => []];

            foreach ($data as $index => $item) {

                $response c= $this->proessItem($item);
                if ($response['ok']) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = ['index' => $index, 'error' => $response['error']];
                }
            }

            return response()->json($results);
        }

        $response = $this->processItem($data);

        if (!$response['ok']) {
            return response()->json(['error' => $response['error']], 404);
        }

        return response()->json(['ok' => true]);
    }

    private function processItem(array $data)
    {
        $validator = Validator::make($data, [
            'fingerprint_id' => ['required'],
            'date' => ['required'],
            'check_in' => ['nullable'],
            'check_out' => ['nullable'],
        ]);

        if ($validator->fails()) {
            Log::warning('Attendance sync: validation failed', $validator->errors()->toArray());
            return ['ok' => false, 'error' => $validator->errors()->first()];
        }

        $validated = $validator->validated();

        $employee = Employee::where('fingerprint_id', $validated['fingerprint_id'])->first();
        dd($employee);
        if (! $employee) {
            Log::warning('Attendance sync: fingerprint not mapped', $validated);
            return ['ok' => false, 'error' => 'Employee not found'];
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

        return ['ok' => true];
    }
}
