<?php

namespace App\Http\Controllers;

use App\Events\AttendanceUpdated;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Services\ShiftScheduler;
use App\Services\TimeNormalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                $response = $this->processItem($item);
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
            'scan_time' => ['required'],
        ]);

        if ($validator->fails()) {
            Log::warning('Attendance sync: validation failed', $validator->errors()->toArray());
            return ['ok' => false, 'error' => $validator->errors()->first()];
        }

        $validated = $validator->validated();

        $employee = Employee::where('fingerprint_id', $validated['fingerprint_id'])->first();

        if (! $employee) {
            Log::warning('Attendance sync: fingerprint not mapped', $validated);
            return ['ok' => false, 'error' => 'Employee not found'];
        }

        DB::transaction(function () use ($employee, $validated) {
            $normalizer = new TimeNormalizer();
            try {
                $norm = $normalizer->normalize($validated['scan_time'], config('app.timezone'), '00:00');
            } catch (\InvalidArgumentException $e) {
                Log::warning('Attendance sync: malformed scan_time', ['scan_time' => $validated['scan_time']]);
                throw $e;
            }
            $date = $norm['date'];
            $time = $norm['time'];
            $dedupMin = max(5, (int) env('ATTENDANCE_DEDUP_MINUTES', 5));
            $minSession = max(30, (int) env('ATTENDANCE_MIN_SESSION_MINUTES', 30));
            $lateThreshold = env('ATTENDANCE_LATE_THRESHOLD', '09:15');
            $earlyThreshold = env('ATTENDANCE_EARLY_THRESHOLD', '17:00');

            $open = AttendanceLog::where('employee_id', $employee->id)
                ->whereNotNull('check_in')
                ->whereNull('check_out')
                ->orderByDesc('date')
                ->lockForUpdate()
                ->first();

            $prevPunchTs = null;
            if ($open) {
                $openDateStr = $open->date instanceof \Illuminate\Support\Carbon ? $open->date->toDateString() : (string) $open->date;
                $prevStr = $open->check_out
                    ? (is_string($open->check_out) ? $open->check_out : $open->check_out->format('H:i'))
                    : (is_string($open->check_in) ? $open->check_in : $open->check_in->format('H:i'));
                $prevPunchTs = strtotime($openDateStr . ' ' . $prevStr);
            } else {
                $lastSameDay = AttendanceLog::where('employee_id', $employee->id)
                    ->whereDate('date', $date)
                    ->orderByDesc('date')
                    ->lockForUpdate()
                    ->first();
                if ($lastSameDay) {
                    $lastDateStr = $lastSameDay->date instanceof \Illuminate\Support\Carbon ? $lastSameDay->date->toDateString() : (string) $lastSameDay->date;
                    $prevStr = $lastSameDay->check_out
                        ? (is_string($lastSameDay->check_out) ? $lastSameDay->check_out : $lastSameDay->check_out->format('H:i'))
                        : (is_string($lastSameDay->check_in) ? $lastSameDay->check_in : $lastSameDay->check_in->format('H:i'));
                    $prevPunchTs = strtotime($lastDateStr . ' ' . $prevStr);
                }
            }
            $curTs = strtotime($date . ' ' . $time);
            if ($prevPunchTs) {
                $diffMin = ($curTs - $prevPunchTs) / 60;
                if ($diffMin >= 0 && $diffMin < $dedupMin) {
                    return;
                }
            }

            $updatedLog = null;
            if ($open) {
                $openDateStr = $open->date instanceof \Illuminate\Support\Carbon ? $open->date->toDateString() : (string) $open->date;
                $ciStr = is_string($open->check_in) ? $open->check_in : $open->check_in->format('H:i');
                $baseTs = strtotime($openDateStr . ' ' . $ciStr);
                $mins = ($curTs - $baseTs) / 60;
                if ($mins >= $minSession && $curTs > $baseTs) {
                    $open->check_out = $time;
                    if (!$open->status || $open->status === 'present') {
                        if ($open->check_in && $open->check_in > $lateThreshold) {
                            $open->status = 'late';
                        }
                        if ($open->check_out && $open->check_out < $earlyThreshold && $open->status !== 'late') {
                            $open->status = 'early_leave';
                        }
                    }
                    $open->source = 'device';
                    $scheduler = new ShiftScheduler();
                    $ciStr = is_string($open->check_in) ? $open->check_in : $open->check_in->format('H:i');
                    $coStr = is_string($open->check_out) ? $open->check_out : $open->check_out->format('H:i');
                    $shift = $open->check_in ? $scheduler->determineShift($ciStr, env('SHIFT_MORNING_START', '06:00'), env('SHIFT_AFTERNOON_START', '15:00')) : null;
                    $flags = $shift ? $scheduler->flags($ciStr, $coStr, $shift, env('SHIFT_MORNING_START', '06:00'), env('SHIFT_AFTERNOON_START', '15:00'), (int) env('SHIFT_GRACE_MINUTES', 15)) : ['early_arrival' => false, 'late_checkin' => false, 'missed_checkout' => false];
                    $open->shift_type = $shift;
                    $open->expected_check_out = null;
                    $open->overtime_minutes = 0;
                    $open->early_arrival = $flags['early_arrival'];
                    $open->late_checkin = $flags['late_checkin'];
                    $open->missed_checkout = $flags['missed_checkout'];
                    $open->save();
                    event(new AttendanceUpdated($open, null));
                    $updatedLog = $open;
                }
            }

            if (! $updatedLog) {
                $log = AttendanceLog::where('employee_id', $employee->id)
                    ->whereDate('date', $date)
                    ->lockForUpdate()
                    ->first();
                if (! $log) {
                    $log = new AttendanceLog(['employee_id' => $employee->id, 'date' => $date]);
                }
                $curCi = $log->check_in ? (is_string($log->check_in) ? $log->check_in : $log->check_in->format('H:i')) : null;
                if (is_null($curCi) || $time < $curCi) {
                    $log->check_in = $time;
                    $log->status = $time > $lateThreshold ? 'late' : 'present';
                    $log->source = 'device';
                    $scheduler = new ShiftScheduler();
                    $ciStr = $time;
                    $coStr = $log->check_out ? (is_string($log->check_out) ? $log->check_out : $log->check_out->format('H:i')) : null;
                    $shift = $scheduler->determineShift($ciStr, env('SHIFT_MORNING_START', '06:00'), env('SHIFT_AFTERNOON_START', '15:00'));
                    $flags = $scheduler->flags($ciStr, $coStr, $shift, env('SHIFT_MORNING_START', '06:00'), env('SHIFT_AFTERNOON_START', '15:00'), (int) env('SHIFT_GRACE_MINUTES', 15));
                    $log->shift_type = $shift;
                    $log->expected_check_out = null;
                    $log->overtime_minutes = 0;
                    $log->early_arrival = $flags['early_arrival'];
                    $log->late_checkin = $flags['late_checkin'];
                    $log->missed_checkout = $flags['missed_checkout'];
                    $log->save();
                    event(new AttendanceUpdated($log, null));
                } else {
                    $ciStr = is_string($log->check_in) ? $log->check_in : $log->check_in->format('H:i');
                    $baseTs = strtotime($date . ' ' . $ciStr);
                    $mins = ($curTs - $baseTs) / 60;
                    $coStr = $log->check_out ? (is_string($log->check_out) ? $log->check_out : $log->check_out->format('H:i')) : null;
                    if (($mins >= $minSession) && ($curTs > $baseTs) && (is_null($coStr) || $time > $coStr)) {
                        $log->check_out = $time;
                        if ($log->check_out && $log->check_out < $earlyThreshold && $log->status !== 'late') {
                            $log->status = 'early_leave';
                        }
                        $log->source = 'device';
                        $scheduler = new ShiftScheduler();
                        $ciStr = is_string($log->check_in) ? $log->check_in : $log->check_in->format('H:i');
                        $coStr = is_string($log->check_out) ? $log->check_out : $log->check_out->format('H:i');
                        $shift = $log->check_in ? $scheduler->determineShift($ciStr, env('SHIFT_MORNING_START', '06:00'), env('SHIFT_AFTERNOON_START', '15:00')) : null;
                        $flags = $shift ? $scheduler->flags($ciStr, $coStr, $shift, env('SHIFT_MORNING_START', '06:00'), env('SHIFT_AFTERNOON_START', '15:00'), (int) env('SHIFT_GRACE_MINUTES', 15)) : ['early_arrival' => false, 'late_checkin' => false, 'missed_checkout' => false];
                        $log->shift_type = $shift;
                        $log->expected_check_out = null;
                        $log->overtime_minutes = 0;
                        $log->early_arrival = $flags['early_arrival'];
                        $log->late_checkin = $flags['late_checkin'];
                        $log->missed_checkout = $flags['missed_checkout'];
                        $log->save();
                        event(new AttendanceUpdated($log, null));
                    }
                }
            }
        });

        return ['ok' => true];
    }
}
