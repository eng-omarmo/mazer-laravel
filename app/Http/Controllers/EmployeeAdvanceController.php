<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeAdvanceController extends Controller
{
    public function index(Request $request)
    {
        $query = EmployeeAdvance::with('employee');
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', (int) $request->input('employee_id'));
        }
        $advances = $query->orderByDesc('date')->paginate(10)->appends($request->query());
        $employees = Employee::orderBy('first_name')->orderBy('last_name')->get();

        return view('hrm.advances', compact('advances', 'employees'));
    }

    public function create()
    {
        $employees = Employee::orderBy('first_name')->orderBy('last_name')->get();

        return view('hrm.advance-create', compact('employees'));
    }

    public function store(Request $request)
    {
        // $this->authorizeRole(['HR', 'Admin']);
        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['nullable', 'string', 'max:255'],
            'installment_amount' => ['nullable', 'numeric', 'min:0.01'],
            'next_due_date' => ['nullable', 'date'],
            'schedule_type' => ['nullable', 'in:none,weekly,biweekly,monthly'],
        ]);
        $adv = EmployeeAdvance::create([
            'employee_id' => (int) $validated['employee_id'],
            'date' => $validated['date'],
            'amount' => (float) $validated['amount'],
            'remaining_balance' => (float) $validated['amount'],
            'installment_amount' => isset($validated['installment_amount']) ? (float) $validated['installment_amount'] : null,
            'next_due_date' => $validated['next_due_date'] ?? null,
            'schedule_type' => $validated['schedule_type'] ?? 'none',
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
        ]);
        \App\Models\AdvanceTransaction::create([
            'advance_id' => $adv->id,
            'type' => 'grant',
            'amount' => (float) $validated['amount'],
            'reference_type' => 'advance',
            'reference_id' => $adv->id,
            'created_by' => \Illuminate\Support\Facades\Auth::id(),
        ]);

        return redirect()->route('hrm.advances.index')->with('status', 'Advance recorded');
    }

    public function show(EmployeeAdvance $advance)
    {
        $advance->load(['employee', 'transactions']);

        return view('hrm.advance-show', compact('advance'));
    }

    public function repay(Request $request, EmployeeAdvance $advance)
    {
        // $this->authorizeRole(['Finance', 'Admin']);
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['nullable', 'date'],
        ]);
        $remaining = (float) ($advance->remaining_balance ?? $advance->amount);
        $amount = (float) $validated['amount'];
        if ($amount > $remaining) {
            return back()->withErrors(['amount' => 'Repayment exceeds remaining balance']);
        }
        $newRemaining = max(0.0, $remaining - $amount);
        $advance->update(['remaining_balance' => $newRemaining]);
        $tx = \App\Models\AdvanceTransaction::create([
            'advance_id' => $advance->id,
            'type' => 'repayment',
            'amount' => $amount,
            'reference_type' => 'manual',
            'reference_id' => $advance->id,
            'created_by' => \Illuminate\Support\Facades\Auth::id(),
        ]);
        if ($newRemaining <= 0 && $advance->status !== 'paid') {
            $advance->update(['status' => 'paid', 'paid_by' => \Illuminate\Support\Facades\Auth::id(), 'paid_at' => now()]);
        } elseif ($advance->schedule_type !== 'none') {
            $advance->update(['next_due_date' => $this->nextDueDate($advance->schedule_type, $advance->next_due_date)]);
        }

        return redirect()->route('hrm.advances.receipt', $tx)->with('status', 'Repayment recorded');
    }

    public function receipt(\App\Models\AdvanceTransaction $transaction)
    {
        $transaction->load('advance.employee');

        return view('hrm.advance-receipt', compact('transaction'));
    }

    private function nextDueDate(string $schedule, ?string $current): string
    {
        $base = $current ? \Carbon\Carbon::parse($current) : now();

        return match ($schedule) {
            'weekly' => $base->copy()->addWeek()->toDateString(),
            'biweekly' => $base->copy()->addWeeks(2)->toDateString(),
            'monthly' => $base->copy()->addMonth()->toDateString(),
            default => $base->toDateString(),
        };
    }

    public function approve(EmployeeAdvance $advance)
    {

        if ($advance->status !== 'pending') {
            return back()->withErrors(['status' => 'Only pending advances can be approved']);
        }
        $advance->update(['status' => 'approved', 'approved_by' => Auth::id(), 'approved_at' => now()]);

        return back()->with('status', 'Advance approved');
    }

    public function markPaid(EmployeeAdvance $advance)
    {
        // $this->authorizeRole(['Finance','Admin']);
        if ($advance->status !== 'approved') {
            return back()->withErrors(['status' => 'Only approved advances can be paid']);
        }
        $wallet = Wallet::main();
        if ($wallet->balance < ($advance->amount ?? 0)) {
            return back()->withErrors(['status' => 'Insufficient wallet balance']);
        }
        $wallet->update(['balance' => $wallet->balance - ($advance->amount ?? 0)]);
        $advance->update(['status' => 'paid', 'paid_by' => Auth::id(), 'paid_at' => now()]);

        return back()->with('status', 'Advance marked as paid');
    }

    private function authorizeRole(array $roles)
    {
        $user = Auth::user();
        if (! $user || ! in_array($user->role ?? 'HR', $roles)) {
            abort(403);
        }
    }
}
