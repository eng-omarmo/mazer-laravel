<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::query()->with(['employee','batch']);
        if ($request->filled('direction')) $query->where('direction', $request->string('direction'));
        if ($request->filled('type')) $query->where('type', $request->string('type'));
        if ($request->filled('status')) $query->where('status', $request->string('status'));
        $transactions = $query->orderByDesc('created_at')->paginate(10)->appends($request->query());
        $balance = Transaction::sum("CASE WHEN direction='credit' THEN amount ELSE 0 END") - Transaction::sum("CASE WHEN direction='debit' THEN amount ELSE 0 END");
        return view('hrm.transactions', compact('transactions','balance'));
    }

    public function create()
    {
        $employees = Employee::orderBy('first_name')->orderBy('last_name')->get();
        return view('hrm.transactions-create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'direction' => ['required','in:credit,debit'],
            'type' => ['required','string','max:50'],
            'amount' => ['required','numeric','min:0.01'],
            'reference' => ['nullable','string','max:255'],
            'employee_id' => ['nullable','exists:employees,id'],
        ]);
        Transaction::create(array_merge($validated, [
            'posted_by' => Auth::id(),
            'posted_at' => now(),
            'status' => 'posted',
        ]));
        return redirect()->route('hrm.transactions.index')->with('status', 'Transaction posted');
    }

    public function edit(Transaction $transaction)
    {
        $employees = Employee::orderBy('first_name')->orderBy('last_name')->get();
        return view('hrm.transactions-edit', compact('transaction','employees'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'direction' => ['required','in:credit,debit'],
            'type' => ['required','string','max:50'],
            'amount' => ['required','numeric','min:0.01'],
            'reference' => ['nullable','string','max:255'],
            'employee_id' => ['nullable','exists:employees,id'],
            'status' => ['required','in:draft,posted,failed'],
        ]);
        $transaction->update($validated);
        return redirect()->route('hrm.transactions.index')->with('status', 'Transaction updated');
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return back()->with('status', 'Transaction deleted');
    }
}