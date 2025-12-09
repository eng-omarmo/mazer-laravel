<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmployeeLeave;
use App\Models\Payroll;
use App\Models\PayrollBatch;
use App\Models\Expense;
use App\Models\ExpensePayment;
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

    public function expenses(Request $request)
    {
        $query = Expense::with(['supplier','organization']);
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->string('payment_status'));
        }
        if ($request->filled('organization_id')) {
            $query->where('organization_id', (int) $request->input('organization_id'));
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', (int) $request->input('supplier_id'));
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->date('to'));
        }

        $items = $query->orderByDesc('created_at')->paginate(10)->appends($request->query());

        // For summary stats, ideally respect filters, but traditionally summary is global or we can make it filtered.
        // Let's make it respect the current filters for better reporting.
        // We need to clone the query because paginate executes it.
        // Actually paginate executes count and get.
        // Let's re-build query for sums or just use global if performance is concern.
        // Given it's a report, filtered stats are more useful.
        
        $statsQuery = Expense::query();
        if ($request->filled('status')) $statsQuery->where('status', $request->string('status'));
        if ($request->filled('payment_status')) $statsQuery->where('payment_status', $request->string('payment_status'));
        if ($request->filled('organization_id')) $statsQuery->where('organization_id', (int) $request->input('organization_id'));
        if ($request->filled('supplier_id')) $statsQuery->where('supplier_id', (int) $request->input('supplier_id'));
        if ($request->filled('from')) $statsQuery->whereDate('created_at', '>=', $request->date('from'));
        if ($request->filled('to')) $statsQuery->whereDate('created_at', '<=', $request->date('to'));

        $totalAmount = $statsQuery->sum('amount');
        
        // Calculating total paid for filtered expenses is tricky because payments are in related table.
        // We can use a join or whereHas.
        // Simple approximation: Sum of amounts of expenses * (if fully paid). But partials exist.
        // Correct way: Sum of approved payments for these expenses.
        
        $expenseIds = $statsQuery->pluck('id');
        $totalPaid = ExpensePayment::whereIn('expense_id', $expenseIds)->where('status', 'approved')->sum('amount');
        
        $totalRemaining = max(0.0, $totalAmount - $totalPaid);
        
        $monthly = Expense::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as ym, SUM(amount) as total')
            ->groupBy('ym')->orderByDesc('ym')->limit(12)->get();

        $suppliers = \App\Models\Supplier::orderBy('name')->get();
        $organizations = \App\Models\Organization::orderBy('name')->get();

        return view('hrm.reports-expenses', compact('items', 'totalAmount', 'totalPaid', 'totalRemaining', 'monthly', 'suppliers', 'organizations'));
    }

    public function expensesCsv(Request $request): StreamedResponse
    {
        $query = Expense::with(['supplier','organization']);
        if ($request->filled('status')) $query->where('status', $request->string('status'));
        if ($request->filled('payment_status')) $query->where('payment_status', $request->string('payment_status'));
        if ($request->filled('organization_id')) $query->where('organization_id', (int) $request->input('organization_id'));
        if ($request->filled('supplier_id')) $query->where('supplier_id', (int) $request->input('supplier_id'));
        if ($request->filled('from')) $query->whereDate('created_at', '>=', $request->date('from'));
        if ($request->filled('to')) $query->whereDate('created_at', '<=', $request->date('to'));

        $rows = $query->orderByDesc('created_at')->get();
        $response = new StreamedResponse(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Type', 'Amount', 'Paid', 'Remaining', 'Status', 'Payment Status', 'Supplier', 'Organization']);
            foreach ($rows as $x) {
                fputcsv($out, [
                    $x->created_at->format('Y-m-d'),
                    $x->type,
                    $x->amount,
                    $x->totalPaid(),
                    $x->remaining(),
                    $x->status,
                    $x->payment_status,
                    optional($x->supplier)->name,
                    optional($x->organization)->name
                ]);
            }
            fclose($out);
        });
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="expenses.csv"');

        return $response;
    }

    public function payments(Request $request)
    {
        $query = ExpensePayment::with(['expense.supplier', 'expense.organization', 'expense']);
        
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('from')) {
            $query->whereDate('paid_at', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('paid_at', '<=', $request->date('to'));
        }
        if ($request->filled('organization_id')) {
            $query->whereHas('expense', function($q) use ($request) {
                $q->where('organization_id', (int) $request->input('organization_id'));
            });
        }
        if ($request->filled('supplier_id')) {
             $query->whereHas('expense', function($q) use ($request) {
                $q->where('supplier_id', (int) $request->input('supplier_id'));
            });
        }

        $items = $query->orderByDesc('paid_at')->paginate(20)->appends($request->query());
        
        $statsQuery = ExpensePayment::query();
        if ($request->filled('status')) $statsQuery->where('status', $request->string('status'));
        if ($request->filled('from')) $statsQuery->whereDate('paid_at', '>=', $request->date('from'));
        if ($request->filled('to')) $statsQuery->whereDate('paid_at', '<=', $request->date('to'));
        if ($request->filled('organization_id')) {
            $statsQuery->whereHas('expense', function($q) use ($request) {
                $q->where('organization_id', (int) $request->input('organization_id'));
            });
        }
        if ($request->filled('supplier_id')) {
             $statsQuery->whereHas('expense', function($q) use ($request) {
                $q->where('supplier_id', (int) $request->input('supplier_id'));
            });
        }

        $totalAmount = $statsQuery->sum('amount');
        $approvedAmount = (clone $statsQuery)->where('status', 'approved')->sum('amount');
        $pendingAmount = (clone $statsQuery)->where('status', 'pending')->sum('amount');

        $suppliers = \App\Models\Supplier::orderBy('name')->get();
        $organizations = \App\Models\Organization::orderBy('name')->get();

        return view('hrm.reports-payments', compact('items', 'totalAmount', 'approvedAmount', 'pendingAmount', 'suppliers', 'organizations'));
    }

    public function paymentsCsv(Request $request): StreamedResponse
    {
        $query = ExpensePayment::with(['expense.supplier', 'expense.organization', 'expense']);
        if ($request->filled('status')) $query->where('status', $request->string('status'));
        if ($request->filled('from')) $query->whereDate('paid_at', '>=', $request->date('from'));
        if ($request->filled('to')) $query->whereDate('paid_at', '<=', $request->date('to'));
        if ($request->filled('organization_id')) {
            $query->whereHas('expense', function($q) use ($request) {
                $q->where('organization_id', (int) $request->input('organization_id'));
            });
        }
        if ($request->filled('supplier_id')) {
             $query->whereHas('expense', function($q) use ($request) {
                $q->where('supplier_id', (int) $request->input('supplier_id'));
            });
        }

        $rows = $query->orderByDesc('paid_at')->get();
        $response = new StreamedResponse(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Amount', 'Note', 'Status', 'Expense ID', 'Supplier', 'Organization']);
            foreach ($rows as $p) {
                fputcsv($out, [
                    $p->paid_at ? $p->paid_at->format('Y-m-d H:i') : '',
                    $p->amount,
                    $p->note,
                    $p->status,
                    $p->expense_id,
                    optional($p->expense->supplier)->name,
                    optional($p->expense->organization)->name
                ]);
            }
            fclose($out);
        });
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="payments.csv"');

        return $response;
    }
}
