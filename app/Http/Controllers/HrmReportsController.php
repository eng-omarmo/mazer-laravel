<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmployeeLeave;
use App\Models\Payroll;
use App\Models\PayrollBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HrmReportsController extends Controller
{
    public function employees(Request $request)
    {
        $query = Employee::query();
        if ($request->filled('department_id')) {
            $query->where('department_id', (int) $request->input('department_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('q')) {
            $needle = '%' . $request->string('q') . '%';
            $query->where(function ($q) use ($needle) {
                $q->where('first_name', 'like', $needle)->orWhere('last_name', 'like', $needle);
            });
        }
        $employees = $query->with('department')->orderBy('first_name')->orderBy('last_name')->paginate(10)->appends($request->query());

        $deptCounts = Employee::selectRaw('department_id, COUNT(*) as cnt')->groupBy('department_id')->get();
        $deptIndex = Department::whereIn('id', $deptCounts->pluck('department_id'))->get()->keyBy('id');
        $headcount = $deptCounts->map(function ($row) use ($deptIndex) {
            $name = optional($deptIndex->get($row->department_id))->name ?? 'Unassigned';

            return ['department' => $name, 'count' => (int) $row->cnt];
        });
        $activeCount = Employee::where('status', 'active')->count();
        $inactiveCount = Employee::where('status', '!=', 'active')->count();

        $ids = $employees->pluck('id');
        $docsTotal = EmployeeDocument::whereIn('employee_id', $ids)->count();
        $docsVerified = EmployeeDocument::whereIn('employee_id', $ids)->where('status', 'verified')->count();
        $complianceRate = $docsTotal > 0 ? round($docsVerified / $docsTotal * 100, 2) : 0;

        $departments = Department::orderBy('name')->get();

        return view('hrm.reports-employees', compact('employees', 'headcount', 'activeCount', 'inactiveCount', 'complianceRate', 'departments'));
    }

    public function employeesCsv(Request $request): StreamedResponse
    {
        $rows = Employee::with('department')->orderBy('first_name')->orderBy('last_name')->get();
        $response = new StreamedResponse(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Department', 'Status']);
            foreach ($rows as $e) {
                fputcsv($out, [$e->first_name . ' ' . $e->last_name, optional($e->department)->name, $e->status]);
            }
            fclose($out);
        });
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="employees.csv"');

        return $response;
    }

    public function leaves(Request $request)
    {
        $query = EmployeeLeave::query()->with('employee');
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('type')) {
            $query->where('type', 'like', '%' . $request->string('type') . '%');
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->integer('employee_id'));
        }
        if ($request->filled('from')) {
            $query->whereDate('start_date', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('end_date', '<=', $request->date('to'));
        }
        $leaves = $query->orderByDesc('created_at')->paginate(10)->appends($request->query());

        $total = EmployeeLeave::count();
        $approved = EmployeeLeave::where('status', 'approved')->count();
        $approvalRate = $total > 0 ? round($approved / $total * 100, 2) : 0;
        $durations = EmployeeLeave::selectRaw('DATEDIFF(end_date, start_date) as days')->whereNotNull('start_date')->whereNotNull('end_date')->pluck('days');
        $avgDuration = $durations->count() ? round($durations->avg(), 2) : 0;
        $pendingAging = EmployeeLeave::where('status', 'pending')->get()->map(function ($l) {
            return now()->diffInDays($l->created_at);
        });
        $avgPendingAging = $pendingAging->count() ? round($pendingAging->avg(), 2) : 0;

        $employees = Employee::orderBy('first_name')->orderBy('last_name')->get();

        return view('hrm.reports-leaves', compact('leaves', 'approvalRate', 'avgDuration', 'avgPendingAging', 'employees'));
    }

    public function leavesCsv(Request $request): StreamedResponse
    {
        $rows = EmployeeLeave::with('employee')->orderByDesc('created_at')->get();
        $response = new StreamedResponse(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Employee', 'Type', 'Status', 'Start', 'End', 'Days']);
            foreach ($rows as $l) {
                $days = $l->start_date && $l->end_date ? $l->end_date->diffInDays($l->start_date) : '';
                fputcsv($out, [optional($l->employee)->first_name . ' ' . optional($l->employee)->last_name, $l->type, $l->status, $l->start_date, $l->end_date, $days]);
            }
            fclose($out);
        });
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="leaves.csv"');

        return $response;
    }

    public function attendance(Request $request)
    {
        $query = AttendanceLog::query()->with(['employee.department']);
        if ($request->filled('date')) {
            $query->where('date', $request->date('date'));
        }
        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->date('to'));
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

        $totalLogs = AttendanceLog::count();
        $presentLogs = AttendanceLog::where('status', 'present')->count();
        $attendanceRate = $totalLogs > 0 ? round($presentLogs / $totalLogs * 100, 2) : 0;
        $lateCounts = AttendanceLog::where('status', 'late')->selectRaw('employee_id, COUNT(*) as c')->groupBy('employee_id')->get();
        $absenceCounts = AttendanceLog::where('status', 'absent')->selectRaw('date, COUNT(*) as c')->groupBy('date')->orderByDesc('date')->limit(30)->get();

        $departments = Department::orderBy('name')->get();

        return view('hrm.reports-attendance', compact('logs', 'attendanceRate', 'lateCounts', 'absenceCounts', 'departments'));
    }

    public function payroll(Request $request)
    {
        $query = Payroll::query()->with('employee');
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('year')) {
            $query->where('year', (int) $request->input('year'));
        }
        if ($request->filled('month')) {
            $query->where('month', (int) $request->input('month'));
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', (int) $request->input('employee_id'));
        }
        if ($request->filled('organization_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('organization_id', (int) $request->input('organization_id'));
            });
        }
        $items = $query->orderByDesc('year')->orderByDesc('month')->paginate(10)->appends($request->query());

        $totalCostByMonth = Payroll::selectRaw('year, month, SUM(net_pay) as total')->groupBy('year', 'month')->orderByDesc('year')->orderByDesc('month')->limit(12)->get();
        $totalAllow = Payroll::sum('allowances');
        $totalDeduct = Payroll::sum('deductions');
        $batchesTotal = PayrollBatch::count();
        $batchesPaid = PayrollBatch::where('status', 'paid')->count();
        $paymentCompletionRate = $batchesTotal > 0 ? round($batchesPaid / $batchesTotal * 100, 2) : 0;
        $approvalVelocities = PayrollBatch::whereNotNull('submitted_at')
            ->whereNotNull('approved_at')
            ->get()
            ->map(function ($b) {
                return Carbon::parse($b->approved_at)
                    ->diffInHours(Carbon::parse($b->submitted_at));
            });

        $avgApprovalHours = $approvalVelocities->count()
            ? round($approvalVelocities->avg(), 2)
            : 0;

        $employees = Employee::orderBy('first_name')->orderBy('last_name')->get();
        $organizations = \App\Models\Organization::orderBy('name')->get();

        return view('hrm.reports-payroll', compact('items', 'totalCostByMonth', 'totalAllow', 'totalDeduct', 'paymentCompletionRate', 'avgApprovalHours', 'employees', 'organizations'));
    }
}
