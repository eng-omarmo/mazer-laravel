<?php

namespace App\Http\Controllers;

use App\Models\AdvanceTransaction;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\Payroll;
use App\Models\Wallet;
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
        $repayment = $this->applyAdvanceRepayments($payroll);
        $payroll->update([
            'advance_deduction' => $repayment['total'],
            'status' => 'paid',
            'paid_by' => Auth::id(),
            'paid_at' => now(),
        ]);
        if ($repayment['total'] > 0) {
            $wallet = Wallet::main();
            $wallet->update(['balance' => $wallet->balance + $repayment['total']]);
        }
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

    private function applyAdvanceRepayments(Payroll $payroll): array
    {
        $employeeId = $payroll->employee_id;
        $available = max(0, (float) $payroll->net_pay);
        $target = is_null($payroll->advance_deduction) ? INF : max(0, (float) $payroll->advance_deduction);
        $totalDeducted = 0.0;
        $details = [];
        if (! $employeeId || $available <= 0) {
            return ['total' => 0.0, 'details' => []];
        }
        $advances = EmployeeAdvance::where('employee_id', $employeeId)
            ->whereIn('status', ['approved'])
            ->orderBy('date')
            ->get();
        foreach ($advances as $adv) {
            if ($available <= 0) {
                break;
            }
            $remainingTarget = $target === INF ? INF : max(0, $target - $totalDeducted);
            $remaining = (float) ($adv->remaining_balance ?: $adv->amount);
            if ($remaining <= 0) {
                if ($adv->status !== 'paid') {
                    $adv->update(['status' => 'paid']);
                }

                continue;
            }
            $installment = (float) ($adv->installment_amount ?: $remaining);
            $deduct = min($installment, $remaining, $available, $remainingTarget);
            if ($deduct <= 0) {
                continue;
            }
            $totalDeducted += $deduct;
            $available -= $deduct;
            $newRemaining = max(0.0, $remaining - $deduct);
            $adv->update(['remaining_balance' => $newRemaining]);
            AdvanceTransaction::create([
                'advance_id' => $adv->id,
                'type' => 'repayment',
                'amount' => $deduct,
                'reference_type' => 'payroll',
                'reference_id' => $payroll->id,
                'created_by' => Auth::id(),
            ]);
            if ($newRemaining <= 0 && $adv->status !== 'paid') {
                $adv->update(['status' => 'paid', 'paid_by' => Auth::id(), 'paid_at' => now()]);
                $details[] = ['advance_id' => $adv->id, 'deducted' => $deduct, 'remaining' => 0, 'fully_repaid' => true];
            } else {
                $details[] = ['advance_id' => $adv->id, 'deducted' => $deduct, 'remaining' => $newRemaining, 'fully_repaid' => false];
            }
            if ($target !== INF && $totalDeducted >= $target) {
                break;
            }
        }

        return ['total' => round($totalDeducted, 2), 'details' => $details];
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
