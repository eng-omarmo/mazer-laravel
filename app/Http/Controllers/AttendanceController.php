<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Department;
use App\Models\Employee;
use App\Events\AttendanceUpdated;
use App\Services\TimeNormalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

            $since = now()->startOfDay();
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
            $boundary = env('ATTENDANCE_DAY_BOUNDARY', '00:00');
            $dedup = max(5, (int) env('ATTENDANCE_DEDUP_MINUTES', 5));
            $minSession = max(30, (int) env('ATTENDANCE_MIN_SESSION_MINUTES', 30));
            $lateThreshold = env('ATTENDANCE_LATE_THRESHOLD', '09:15');
            $earlyThreshold = env('ATTENDANCE_EARLY_THRESHOLD', '17:00');

            $normalizer = new TimeNormalizer();
            $until = (clone $since)->addDay();
            foreach ($logs as $log) {
                // Parse time
                $timeStr = $log['time'] ?? $log['attTime'] ?? null;
                if (!$timeStr) continue;

                try {
                    $norm = $normalizer->normalize($timeStr, config('app.timezone'), $boundary);
                } catch (\InvalidArgumentException $e) {
                    Log::warning('Skipping malformed device timestamp', ['raw' => $timeStr, 'error' => $e->getMessage()]);
                    continue;
                }
                if (! $normalizer->withinWindow($norm['local'], $since, $until)) {
                    continue;
                }
                $date = $norm['date'];
                $time = $norm['time'];
                $userId = $log['userId'] ?? $log['uid'] ?? 0;

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

                DB::transaction(function () use ($employee, $date, $time, $lateThreshold, $earlyThreshold, $minSession, $dedup) {
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
                        if ($diffMin >= 0 && $diffMin < $dedup) {
                            return;
                        }
                    }

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
                            $open->save();
                            event(new AttendanceUpdated($open, null));
                            return;
                        }
                    }

                    $attLog = AttendanceLog::where('employee_id', $employee->id)->whereDate('date', $date)->lockForUpdate()->first();
                    if (! $attLog) {
                        $attLog = new AttendanceLog(['employee_id' => $employee->id, 'date' => $date]);
                    }
                    $curCi = $attLog->check_in ? (is_string($attLog->check_in) ? $attLog->check_in : $attLog->check_in->format('H:i')) : null;
                    if (is_null($curCi) || $time < $curCi) {
                        $attLog->check_in = $time;
                        $attLog->status = $time > $lateThreshold ? 'late' : 'present';
                        $attLog->source = 'device';
                        $attLog->save();
                        event(new AttendanceUpdated($attLog, null));
                    } else {
                        $ciStr = is_string($attLog->check_in) ? $attLog->check_in : $attLog->check_in->format('H:i');
                        $baseTs = strtotime($date . ' ' . $ciStr);
                        $mins = ($curTs - $baseTs) / 60;
                        $coStr = $attLog->check_out ? (is_string($attLog->check_out) ? $attLog->check_out : $attLog->check_out->format('H:i')) : null;
                        if (($mins >= $minSession) && ($curTs > $baseTs) && (is_null($coStr) || $time > $coStr)) {
                            $attLog->check_out = $time;
                            if ($attLog->check_out && $attLog->check_out < $earlyThreshold && $attLog->status !== 'late') {
                                $attLog->status = 'early_leave';
                            }
                            $attLog->source = 'device';
                            $attLog->save();
                            event(new AttendanceUpdated($attLog, null));
                        }
                    }
                });
            }

            $msg = "Synced successfully for today. Fetched $totalFetched logs. Created $count new records, updated $updatedCount records.";
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
