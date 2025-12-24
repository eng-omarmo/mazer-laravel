<?php

namespace App\Http\Controllers;

use App\Models\AdvanceTransaction;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\Payroll;
use App\Models\PayrollBatch;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\MerchantPayService;

class PayrollBatchController extends Controller
{

    protected MerchantPayService $merchantPayService;
    public function __construct(MerchantPayService $merchantPayService)
    {
        $this->merchantPayService = $merchantPayService;
    }
    public function index(Request $request)
    {
        $query = PayrollBatch::query();
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('year')) {
            $query->where('year', (int) $request->input('year'));
        }
        if ($request->filled('month')) {
            $query->where('month', (int) $request->input('month'));
        }
        $batches = $query->orderByDesc('year')->orderByDesc('month')->paginate(10)->appends($request->query());

        return view('hrm.payroll-batches', compact('batches'));
    }

    public function create(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $preview = $request->filled('preview');
        $employees = collect();
        if ($preview) {
            $employees = Employee::where('status', 'active')->orderBy('first_name')->orderBy('last_name')->get();
        }
        return view('hrm.payroll-post', compact('year', 'month', 'preview', 'employees'));
    }

    public function store(Request $request)
    {

        $this->authorizeRole(['finance', 'admin']);
        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'lines' => ['required', 'array'],
        ]);

        if (PayrollBatch::where('year', $validated['year'])->where('month', $validated['month'])->exists()) {
            return back()->withErrors(['month' => 'Payroll batch for selected month already exists']);
        }

        $batch = PayrollBatch::create([
            'year' => $validated['year'],
            'month' => $validated['month'],
            'status' => 'draft',
            'posted_by' => Auth::id(),
            'posted_at' => now(),
        ]);

        $totalEmployees = 0;
        $totalAmount = 0;

        foreach ($validated['lines'] as $employeeId => $line) {
            $basic = (float) ($line['basic_salary'] ?? 0);
            $allow = (float) ($line['allowances'] ?? 0);
            $deduct = (float) ($line['deductions'] ?? 0);
            $net = $basic + $allow - $deduct;
            $advances = \App\Models\EmployeeAdvance::where('employee_id', (int) $employeeId)
                ->whereIn('status', ['approved'])
                ->orderBy('date')
                ->get();
            $remainingTotal = $advances->sum(function ($a) {
                return (float) ($a->remaining_balance ?? $a->amount);
            });
            $sumInstallments = $advances->sum(function ($a) {
                $rem = (float) ($a->remaining_balance ?? $a->amount);
                $inst = (float) ($a->installment_amount ?? $rem);

                return min($inst, $rem);
            });
            $plannedAdv = isset($line['advance_deduction']) ? (float) $line['advance_deduction'] : 0.0;
            Payroll::create([
                'batch_id' => $batch->id,
                'employee_id' => (int) $employeeId,
                'year' => $validated['year'],
                'month' => $validated['month'],
                'basic_salary' => $basic,
                'allowances' => $allow,
                'deductions' => $deduct,
                'net_pay' => $net,
                'advance_deduction' => $plannedAdv,
                'status' => 'draft',
                ''
            ]);
            $totalEmployees++;
            $totalAmount += $net;
        }

        $batch->update([
            'total_employees' => $totalEmployees,
            'total_amount' => $totalAmount,
        ]);

        return redirect()->route('hrm.payroll.batches.show', $batch)->with('status', 'Payroll batch created');
    }

    public function show(PayrollBatch $batch)
    {
        $batch->load(['payrolls.employee']);

        return view('hrm.payroll-batch-show', compact('batch'));
    }

    public function update(Request $request, PayrollBatch $batch)
    {
        $this->authorizeRole(['finance', 'admin']);
        if ($batch->status === 'approved') {
            return back()->withErrors(['status' => 'Approved batches are locked']);
        }
        $validated = $request->validate([
            'lines' => ['required', 'array'],
        ]);
        $totalAmount = 0;
        foreach ($batch->payrolls as $p) {
            $line = $validated['lines'][$p->id] ?? null;
            if (! $line) {
                continue;
            }
            $basic = (float) ($line['basic_salary'] ?? $p->basic_salary);
            $allow = (float) ($line['allowances'] ?? $p->allowances);
            $deduct = (float) ($line['deductions'] ?? $p->deductions);
            $net = $basic + $allow - $deduct;
            $plannedAdv = isset($line['advance_deduction']) ? max(0, (float) $line['advance_deduction']) : ($p->advance_deduction ?? 0.0);
            // Clamp plannedAdv by per-employee advance constraints and net
            $advances = EmployeeAdvance::where('employee_id', (int) $p->employee_id)
                ->whereIn('status', ['approved'])
                ->orderBy('date')
                ->get();
            $remainingTotal = $advances->sum(function ($a) {
                return (float) ($a->remaining_balance ?? $a->amount);
            });
            $sumInstallments = $advances->sum(function ($a) {
                $rem = (float) ($a->remaining_balance ?? $a->amount);
                $inst = (float) ($a->installment_amount ?? $rem);
                return min($inst, $rem);
            });
            $plannedAdv = min($plannedAdv, $sumInstallments, $remainingTotal, $net);
            $p->update([
                'basic_salary' => $basic,
                'allowances' => $allow,
                'deductions' => $deduct,
                'net_pay' => $net,
                'advance_deduction' => $plannedAdv,
            ]);
            $totalAmount += $net;
        }
        $batch->update(['total_amount' => $totalAmount]);

        return back()->with('status', 'Batch updated');
    }

    public function submit(PayrollBatch $batch)
    {
        $this->authorizeRole(['finance', 'admin']);
        if ($batch->status !== 'draft') {
            return back()->withErrors(['status' => 'Only draft batches can be submitted']);
        }
        $batch->update(['status' => 'submitted', 'submitted_by' => Auth::id(), 'submitted_at' => now()]);

        return back()->with('status', 'Batch submitted');
    }

    public function approve(PayrollBatch $batch)
    {
        $this->authorizeRole(['hrm', 'admin']);
        if ($batch->status !== 'submitted') {
            return back()->withErrors(['status' => 'Only submitted batches can be approved']);
        }
        $wallet = Wallet::main();
        if ($wallet->balance < ($batch->total_amount ?? 0)) {
            return back()->withErrors(['status' => 'Insufficient wallet balance']);
        }
        $wallet->update(['balance' => $wallet->balance - ($batch->total_amount ?? 0)]);
        $batch->update(['status' => 'approved', 'approved_by' => Auth::id(), 'approved_at' => now()]);
        foreach ($batch->payrolls as $p) {
            $p->update(['status' => 'approved', 'approved_by' => Auth::id(), 'approved_at' => now()]);
        }

        return back()->with('status', 'Batch approved');
    }

    public function reject(PayrollBatch $batch)
    {
        $this->authorizeRole(['hrm', 'admin']);
        if ($batch->status !== 'submitted') {
            return back()->withErrors(['status' => 'Only submitted batches can be rejected']);
        }
        $batch->update(['status' => 'rejected', 'rejected_by' => Auth::id(), 'rejected_at' => now()]);

        return back()->with('status', 'Batch rejected');
    }

    public function markPaid(PayrollBatch $batch)
    {

        $this->authorizeRole(['finance', 'admin']);
        if ($batch->status !== 'approved') {
            return back()->withErrors(['status' => 'Only approved batches can be paid']);
        }
        $walletCredit = 0.0;
        foreach ($batch->payrolls as $p) {
            $data = [
                'receiver' => $p->employee->phone,
                'amount' => (float) $p->net_pay,
                'payment_method' => 'Hormuud',
                'reference' => 'EMP-' . $p->employee_id . '-PAY-' . $batch->year . '-' . $batch->month,
            ];
            $this->excute($data);
            if ($p->status !== 'paid') {
                $repayment = $this->applyAdvanceRepayments($p);
                $p->update([
                    'advance_deduction' => $repayment['total'],
                    'status' => 'paid',
                    'paid_by' => Auth::id(),
                    'paid_at' => now(),
                ]);
                $walletCredit += $repayment['total'];
            }
        }
        if ($walletCredit > 0) {
            $wallet = Wallet::main();
            $wallet->update(['balance' => $wallet->balance + $walletCredit]);
        }
        $batch->update(['status' => 'paid', 'paid_by' => Auth::id(), 'paid_at' => now()]);


        return back()->with('status', 'Batch marked as paid');
    }

    private function excute($bulkPayments){
             try {
            $this->merchantPayService->executeTransaction($bulkPayments);
        } catch (\Throwable $e) {
            logger()->error('MerchantPay verify failed', [
                'userId' => Auth::id(),
                'exception' => (string) $e,
            ]);
            return back()->withErrors(['status' => 'External payment gateway failed']);
        }
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

    public function approveAllPending(Request $request)
    {
        // $this->authorizeRole(['Finance', 'Admin']);
        $wallet = Wallet::main();
        $approved = 0;
        $skipped = 0;
        $submittedBatches = PayrollBatch::where('status', 'draft')->orderByDesc('year')->orderByDesc('month')->get();

        foreach ($submittedBatches as $batch) {
            $amount = (float) ($batch->total_amount ?? 0);
            if ($wallet->balance >= $amount) {
                $wallet->update(['balance' => $wallet->balance - $amount]);
                $batch->update(['status' => 'approved', 'approved_by' => Auth::id(), 'approved_at' => now()]);
                foreach ($batch->payrolls as $p) {
                    $p->update(['status' => 'approved', 'approved_by' => Auth::id(), 'approved_at' => now()]);
                }
                $approved++;
            } else {
                $skipped++;
            }
        }
        $message = $approved > 0 ? "Approved {$approved} submitted batch(es)" : 'No submitted batches approved';
        if ($skipped > 0) {
            $message .= "; Skipped {$skipped} due to insufficient wallet balance";
        }

        return redirect()->route('hrm.payroll.batches.index')->with('status', $message);
    }

    public function markPaidAllApproved(Request $request)
    {

        // validate date and year then add that to query
        $request->validate([
            'year' => 'required|integer|min:2020|max:' . date('Y'),
            'month' => 'required|integer|min:1|max:12',
        ]);
        $this->authorizeRole(['finance', 'admin']);
        $approvedBatches = PayrollBatch::where('status', 'approved')
            ->where('year', $request->year)
            ->where('month', $request->month)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();
        $paid = 0;
        foreach ($approvedBatches as $batch) {
            $walletCreditBatch = 0.0;
            foreach ($batch->payrolls as $p) {
                if ($p->status !== 'paid') {
                    $repayment = $this->applyAdvanceRepayments($p);
                    $p->update([
                        'advance_deduction' => $repayment['total'],
                        'status' => 'paid',
                        'paid_by' => Auth::id(),
                        'paid_at' => now(),
                    ]);
                    $walletCreditBatch += $repayment['total'];
                }
            }
            if ($walletCreditBatch > 0) {
                $wallet = Wallet::main();
                $wallet->update(['balance' => $wallet->balance + $walletCreditBatch]);
            }

            if ($batch->status !== 'paid') {
                $batch->update(['status' => 'paid', 'paid_by' => Auth::id(), 'paid_at' => now()]);
                $paid++;
            }
        }
        $message = $paid > 0 ? "Marked {$paid} approved batch(es) as paid" : 'No approved batches to mark as paid';

        return redirect()->route('hrm.payroll.batches.index')->with('status', $message);
    }

    private function authorizeRole(array $roles)
    {
        $user = Auth::user();
        $role = strtolower($user->role ?? 'hrm');
        $allowed = array_map('strtolower', $roles);
        if (! $user || ! in_array($role, $allowed)) {
            abort(403);
        }
    }
}
