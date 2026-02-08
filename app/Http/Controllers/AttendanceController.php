<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = AttendanceLog::query()->with(['employee.department']);
        if ($request->filled('date')) {
            $query->where('date', $request->date('date'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', (int) $request->input('department_id'));
            });
        }
        $logs = $query->orderByDesc('date')->orderBy('employee_id')->paginate(10)->appends($request->query());
        $departments = Department::orderBy('name')->get();

        return view('hrm.attendance', compact('logs', 'departments'));
    }

    public function sync(Request $request)
    {
        try {
            $client = new \GuzzleHttp\Client();
            $agentUrl = env('ZK_AGENT_URL', 'http://127.0.0.1:8282');

            // Calculate 24-hour window
            $since = now()->subHours(24);
            $sinceStr = $since->toDateTimeString();

            Log::info("Starting attendance sync from $agentUrl (Since: $sinceStr)");

            $response = $client->get("$agentUrl/logs", [
                'headers' => [
                    'X-AGENT-KEY' => env('FINGERPRINT_AGENT_KEY', '')
                ],
                'query' => [
                    'since' => $sinceStr
                ],
                'timeout' => 20
            ]);

            $result = json_decode($response->getBody(), true);

            if (!$result || !isset($result['ok']) || !$result['ok']) {
                $err = $result['error'] ?? 'Unknown error from agent';
                Log::error("Attendance sync failed: $err");
                throw new \Exception($err);
            }

            $logs = $result['data'] ?? [];
            $totalFetched = count($logs);
            Log::info("Fetched $totalFetched logs from device (last 24h).");

            // Sort logs by time to ensure correct check-in/out order
            usort($logs, function ($a, $b) {
                $t1 = $a['time'] ?? $a['attTime'] ?? '';
                $t2 = $b['time'] ?? $b['attTime'] ?? '';
                return strcmp($t1, $t2);
            });

            $count = 0;
            $updatedCount = 0;
            $processedEmployees = [];
            $lastTimes = [];
            $boundary = env('ATTENDANCE_DAY_BOUNDARY', '04:00');
            $dedup = (int) env('ATTENDANCE_DEDUP_MINUTES', 2);
            $minSession = (int) env('ATTENDANCE_MIN_SESSION_MINUTES', 30);
            $lateThreshold = env('ATTENDANCE_LATE_THRESHOLD', '09:15');
            $earlyThreshold = env('ATTENDANCE_EARLY_THRESHOLD', '17:00');

            foreach ($logs as $log) {
                // Parse time
                $timeStr = $log['time'] ?? $log['attTime'] ?? null;
                if (!$timeStr) continue;

                $ts = strtotime($timeStr);
                $date = date('Y-m-d', $ts);
                $time = date('H:i', $ts);
                $userId = $log['userId'] ?? $log['uid'] ?? 0;

                // Ensure log is within the last 24 hours (double check)
                if ($timeStr < $sinceStr) {
                    continue;
                }

                if ($time < $boundary) {
                    $date = date('Y-m-d', strtotime('-1 day', $ts));
                }

                // Find employee by fingerprint_id (which is usually the ID on the device)
                $employee = Employee::where('fingerprint_id', $userId)->first();

                if (!$employee) {
                    // Optional: Log missing employee once per user ID to avoid spam
                    if (!isset($processedEmployees["missing_$userId"])) {
                        Log::warning("Sync: No employee found for device User ID: $userId");
                        $processedEmployees["missing_$userId"] = true;
                    }
                    continue;
                }

                $prev = $lastTimes[$employee->id][$date] ?? null;
                if ($prev) {
                    $prevTs = strtotime($date . ' ' . $prev);
                    $curTs = strtotime($date . ' ' . $time);
                    $diffMin = ($curTs - $prevTs) / 60;
                    if ($diffMin < $dedup) {
                        continue;
                    }
                }
                $lastTimes[$employee->id][$date] = $time;

                $attLog = AttendanceLog::firstOrNew([
                    'employee_id' => $employee->id,
                    'date' => $date
                ]);

                $isNew = !$attLog->exists;
                $updated = false;
                $deviceStatus = isset($log['status']) ? (int)$log['status'] : 255; // 0: Check-In, 1: Check-Out, 255: Default

                // Logic:
                // 1. If explicit Status 0 (Check-In) -> Update Check-In
                // 2. If explicit Status 1 (Check-Out) -> Update Check-Out
                // 3. Fallback (Status 255 or others): First log = In, Last log = Out

                if ($isNew) {
                    // New Record
                    if ($deviceStatus === 1) {
                        // Weird case: First log is Check-Out? Treat as Check-Out (Check-In might be missing)
                        $attLog->check_out = $time;
                    } else {
                        // Default to Check-In
                        $attLog->check_in = $time;
                    }
                    $attLog->status = 'present';
                    $attLog->source = 'device';
                    $updated = true;
                    $count++;
                } else {
                    // Existing Record
                    if ($deviceStatus === 0) {
                         // Explicit Check-In
                         // Only update if earlier than existing check-in (or if check-in is missing)
                         if (is_null($attLog->check_in) || $time < $attLog->check_in) {
                             $attLog->check_in = $time;
                             $updated = true;
                             $updatedCount++;
                         }
                    } elseif ($deviceStatus === 1) {
                         // Explicit Check-Out
                         // Always update Check-Out if this is a check-out log (and it's later than check-in)
                         // Or if check-out is missing
                         if (is_null($attLog->check_out) || $time > $attLog->check_out) {
                             // Ensure it's not before check-in (unless check-in is missing)
                             if (is_null($attLog->check_in) || $time > $attLog->check_in) {
                                 if (!is_null($attLog->check_in)) {
                                     $baseTs = strtotime($date . ' ' . $attLog->check_in);
                                     $curTs = strtotime($date . ' ' . $time);
                                     $mins = ($curTs - $baseTs) / 60;
                                     if ($mins >= $minSession) {
                                         $attLog->check_out = $time;
                                         $updated = true;
                                         $updatedCount++;
                                     }
                                 } else {
                                     $attLog->check_out = $time;
                                     $updated = true;
                                     $updatedCount++;
                                 }
                             }
                         }
                    } else {
                        // Fallback: Time-based logic for Status 255/Other

                        // If current log time is EARLIER than existing check_in, update check_in
                        if (!is_null($attLog->check_in) && $time < $attLog->check_in) {
                            $attLog->check_in = $time;
                            $updated = true;
                            $updatedCount++;
                        }
                        // If current log time is LATER than existing check_in, consider it for check_out
                        elseif (!is_null($attLog->check_in) && $time > $attLog->check_in) {
                            // Update check_out if it's null OR this time is later than existing check_out
                            if (is_null($attLog->check_out) || $time > $attLog->check_out) {
                                $baseTs = strtotime($date . ' ' . $attLog->check_in);
                                $curTs = strtotime($date . ' ' . $time);
                                $mins = ($curTs - $baseTs) / 60;
                                if ($mins >= $minSession) {
                                    $attLog->check_out = $time;
                                    $updated = true;
                                    $updatedCount++;
                                }
                            }
                        }
                    }
                }

                if ($updated) {
                    // Recalculate Status
                    // Rule: Late if check_in > 09:15
                    if ($attLog->check_in && $attLog->check_in > $lateThreshold) {
                        $attLog->status = 'late';
                    }
                    // Rule: Early Leave if check_out < 17:00 (only if check_out exists)
                    if ($attLog->check_out && $attLog->check_out < $earlyThreshold && $attLog->status !== 'late') {
                         if ($attLog->status == 'present') {
                             $attLog->status = 'early_leave';
                         }
                    }
                    // Rule: Reset to present if conditions met (e.g. fixed invalid status)
                    if ($attLog->check_in <= $lateThreshold && (!$attLog->check_out || $attLog->check_out >= $earlyThreshold)) {
                        $attLog->status = 'present';
                    }

                    $attLog->save();
                }
            }

            $msg = "Synced successfully. Fetched $totalFetched logs from last 24h. Created $count new records, updated $updatedCount records.";
            return back()->with('status', $msg);
        } catch (\Exception $e) {
            Log::error('Attendance Sync Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Sync failed: ' . $e->getMessage()]);
        }
    }

    public function create()
    {
        $employees = Employee::orderBy('first_name')->orderBy('last_name')->get();

        return view('hrm.attendance-edit', ['employees' => $employees, 'log' => null]);
    }

    public function store(Request $request)
    {
        $this->authorizeRole(['HR', 'Admin']);
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'date' => ['required', 'date'],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i'],
            'status' => ['required', 'in:present,absent,late,early_leave'],
            'source' => ['required', 'in:manual,device'],
        ]);
        if (AttendanceLog::where('employee_id', $validated['employee_id'])->where('date', $validated['date'])->exists()) {
            return back()->withErrors(['date' => 'Attendance already recorded for this employee and date']);
        }
        AttendanceLog::create($validated);

        return redirect()->route('hrm.attendance.index')->with('status', 'Attendance saved');
    }

    public function edit(AttendanceLog $log)
    {
        $employees = Employee::orderBy('first_name')->orderBy('last_name')->get();

        return view('hrm.attendance-edit', compact('log', 'employees'));
    }

    public function update(Request $request, AttendanceLog $log)
    {
        $this->authorizeRole(['HR', 'Admin']);
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'date' => ['required', 'date'],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i'],
            'status' => ['required', 'in:present,absent,late,early_leave'],
            'source' => ['required', 'in:manual,device'],
        ]);
        if ($log->employee_id != $validated['employee_id'] || $log->date != $validated['date']) {
            if (AttendanceLog::where('employee_id', $validated['employee_id'])->where('date', $validated['date'])->exists()) {
                return back()->withErrors(['date' => 'Attendance already recorded for this employee and date']);
            }
        }
        $log->update($validated);

        return redirect()->route('hrm.attendance.index')->with('status', 'Attendance updated');
    }

    public function summary(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $start = now()->setDate($year, $month, 1)->startOfMonth();
        $end = (clone $start)->endOfMonth();
        $employees = Employee::with('department')->orderBy('first_name')->orderBy('last_name')->get();
        $data = [];
        foreach ($employees as $e) {
            $logs = AttendanceLog::where('employee_id', $e->id)->whereBetween('date', [$start->toDateString(), $end->toDateString()])->get();
            $present = $logs->where('status', 'present')->count();
            $absent = $logs->where('status', 'absent')->count();
            $late = $logs->where('status', 'late')->count();
            $early = $logs->where('status', 'early_leave')->count();
            $data[] = compact('e', 'present', 'absent', 'late', 'early');
        }

        return view('hrm.attendance-summary', compact('year', 'month', 'data'));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $filename = "attendance_{$year}_" . str_pad($month, 2, '0', STR_PAD_LEFT) . '.csv';
        $response = new StreamedResponse(function () use ($year, $month) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Employee', 'Department', 'Present', 'Absent', 'Late', 'Early Leave']);
            $start = now()->setDate($year, $month, 1)->startOfMonth();
            $end = (clone $start)->endOfMonth();
            $emps = Employee::with('department')->get();
            foreach ($emps as $e) {
                $logs = AttendanceLog::where('employee_id', $e->id)->whereBetween('date', [$start->toDateString(), $end->toDateString()])->get();
                fputcsv($out, [
                    $e->first_name . ' ' . $e->last_name,
                    optional($e->department)->name,
                    $logs->where('status', 'present')->count(),
                    $logs->where('status', 'absent')->count(),
                    $logs->where('status', 'late')->count(),
                    $logs->where('status', 'early_leave')->count(),
                ]);
            }
            fclose($out);
        });
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    public function myHistory(Request $request)
    {
        $user = Auth::user();
        $employee = Employee::where('email', $user->email)->first();
        if (! $employee) {
            session()->flash('status', 'No employee record linked to your account. Please contact HR to link your profile.');
            $logs = AttendanceLog::where('employee_id', 0)->orderByDesc('date')->paginate(10);

            return view('hrm.attendance', ['logs' => $logs, 'departments' => collect()]);
        }
        $logs = AttendanceLog::where('employee_id', $employee->id)->orderByDesc('date')->paginate(10);

        return view('hrm.attendance', ['logs' => $logs, 'departments' => collect()]);
    }

    private function authorizeRole(array $roles)
    {
        $user = Auth::user();
        if (! $user || ! in_array($user->role ?? 'HR', $roles)) {
            abort(403);
        }
    }
}
