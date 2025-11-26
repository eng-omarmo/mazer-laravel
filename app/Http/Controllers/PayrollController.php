<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayrollController extends Controller
{
    public function index(Request $request)
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
        $payrolls = $query->orderByDesc('year')->orderByDesc('month')->paginate(10)->appends($request->query());
        $employees = Employee::orderBy('first_name')->orderBy('last_name')->get();

        return view('hrm.payroll', compact('payrolls', 'employees'));
    }

    public function create()
    {
        $employees = Employee::orderBy('first_name')->orderBy('last_name')->get();

        return view('hrm.payroll-create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'allowances' => ['nullable', 'numeric', 'min:0'],
            'deductions' => ['nullable', 'numeric', 'min:0'],
        ]);
        $allow = (float) ($validated['allowances'] ?? 0);
        $deduct = (float) ($validated['deductions'] ?? 0);
        $net = (float) $validated['basic_salary'] + $allow - $deduct;
        Payroll::create(array_merge($validated, [
            'allowances' => $allow,
            'deductions' => $deduct,
            'net_pay' => $net,
            'status' => 'draft',
        ]));

        return redirect()->route('hrm.payroll.index')->with('status', 'Payroll created');
    }

    public function edit(Payroll $payroll)
    {
        $employees = Employee::orderBy('first_name')->orderBy('last_name')->get();

        return view('hrm.payroll-edit', compact('payroll', 'employees'));
    }

    public function update(Request $request, Payroll $payroll)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'allowances' => ['nullable', 'numeric', 'min:0'],
            'deductions' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:draft,approved,paid'],
        ]);
        $net = (float) $validated['basic_salary'] + (float) ($validated['allowances'] ?? 0) - (float) ($validated['deductions'] ?? 0);
        $payroll->update(array_merge($validated, ['net_pay' => $net]));
        $this->recalcBatchTotals($payroll);

        return redirect()->route('hrm.payroll.index')->with('status', 'Payroll updated');
    }

    public function destroy(Payroll $payroll)
    {
        $batchId = $payroll->batch_id;
        $payroll->delete();
        if ($batchId) {
            $batchPayroll = new Payroll;
            $batchPayroll->batch_id = $batchId;
            $this->recalcBatchTotals($batchPayroll);
        }

        return back()->with('status', 'Payroll deleted');
    }

    public function approve(Payroll $payroll)
    {
        $payroll->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('status', 'Payroll approved');
    }

    public function markPaid(Payroll $payroll)
    {
        $payroll->update([
            'status' => 'paid',
            'paid_by' => Auth::id(),
            'paid_at' => now(),
        ]);
        if ($payroll->batch_id) {
            $batch = $payroll->batch;
            if ($batch) {
                $allPaid = $batch->payrolls()->where('status', '!=', 'paid')->count() === 0;
                if ($allPaid && $batch->status !== 'paid') {
                    $batch->update(['status' => 'paid', 'paid_by' => Auth::id(), 'paid_at' => now()]);
                }
                $this->recalcBatchTotals($payroll);
            }
        }

        return back()->with('status', 'Payroll marked as paid');
    }

    private function recalcBatchTotals(Payroll $payroll)
    {
        if (! $payroll->batch_id) {
            return;
        }
        $batch = $payroll->batch()->first();
        if (! $batch) {
            return;
        }
        $totalAmount = $batch->payrolls()->sum('net_pay');
        $totalEmployees = $batch->payrolls()->count();
        $batch->update([
            'total_amount' => $totalAmount,
            'total_employees' => $totalEmployees,
        ]);
    }
}
