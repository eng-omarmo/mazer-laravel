<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $filename = "attendance_{$year}_".str_pad($month, 2, '0', STR_PAD_LEFT).'.csv';
        $response = new StreamedResponse(function () use ($year, $month) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Employee', 'Department', 'Present', 'Absent', 'Late', 'Early Leave']);
            $start = now()->setDate($year, $month, 1)->startOfMonth();
            $end = (clone $start)->endOfMonth();
            $emps = Employee::with('department')->get();
            foreach ($emps as $e) {
                $logs = AttendanceLog::where('employee_id', $e->id)->whereBetween('date', [$start->toDateString(), $end->toDateString()])->get();
                fputcsv($out, [
                    $e->first_name.' '.$e->last_name,
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
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');

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
