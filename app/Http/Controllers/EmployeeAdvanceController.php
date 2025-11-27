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
        $this->authorizeRole(['HR', 'Admin']);
        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);
        EmployeeAdvance::create([
            'employee_id' => (int) $validated['employee_id'],
            'date' => $validated['date'],
            'amount' => (float) $validated['amount'],
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('hrm.advances.index')->with('status', 'Advance recorded');
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
