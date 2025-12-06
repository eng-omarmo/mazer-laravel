<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Supplier;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ExpensePayment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['supplier','organization']);
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', (int) $request->input('supplier_id'));
        }
        if ($request->filled('organization_id')) {
            $query->where('organization_id', (int) $request->input('organization_id'));
        }
        $expenses = $query->orderByDesc('created_at')->paginate(10)->appends($request->query());
        $suppliers = Supplier::orderBy('name')->get();
        $organizations = Organization::orderBy('name')->get();

        return view('hrm.expenses', compact('expenses', 'suppliers', 'organizations'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $organizations = Organization::orderBy('name')->get();

        return view('hrm.expenses-create', compact('suppliers', 'organizations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'status' => ['required', 'string', 'in:pending,reviewed,approved'],
            'upload_document' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png'],
        ]);

        $docPath = null;
        if ($request->hasFile('upload_document')) {
            $file = $request->file('upload_document');
            $name = 'expense_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $docPath = Storage::disk('public')->putFileAs('expense_docs', $file, $name);
        }

        Expense::create([
            'supplier_id' => $validated['supplier_id'] ?? null,
            'organization_id' => $validated['organization_id'] ?? null,
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'document_path' => $docPath,
            'status' => $validated['status'],
        ]);

        return redirect()->route('hrm.expenses.index')->with('status', 'Expense created');
    }

    public function edit(Expense $expense)
    {
        $suppliers = Supplier::orderBy('name')->get();
        $organizations = Organization::orderBy('name')->get();

        return view('hrm.expenses-edit', compact('expense', 'suppliers', 'organizations'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'status' => ['required', 'string', 'in:pending,reviewed,approved'],
            'upload_document' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png'],
        ]);

        $docPath = $expense->document_path;
        if ($request->hasFile('upload_document')) {
            $file = $request->file('upload_document');
            $name = 'expense_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $docPath = Storage::disk('public')->putFileAs('expense_docs', $file, $name);
        }

        $expense->update([
            'supplier_id' => $validated['supplier_id'] ?? null,
            'organization_id' => $validated['organization_id'] ?? null,
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'document_path' => $docPath,
            'status' => $validated['status'],
        ]);

        return redirect()->route('hrm.expenses.index')->with('status', 'Expense updated');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return back()->with('status', 'Expense deleted');
    }

    public function review(Expense $expense)
    {
        $expense->update(['status' => 'reviewed']);

        return back()->with('status', 'Expense marked reviewed');
    }

    public function approve(Expense $expense)
    {
        $expense->update(['status' => 'approved']);

        return back()->with('status', 'Expense approved');
    }

    public function show(Expense $expense)
    {
        $expense->load(['supplier','organization','payments','payments.expense']);
        $suppliers = Supplier::orderBy('name')->get();
        $organizations = Organization::orderBy('name')->get();

        return view('hrm.expenses-show', compact('expense','suppliers','organizations'));
    }

    public function pay(Request $request, Expense $expense)
    {
        $role = strtolower(auth()->user()->role ?? 'hrm');
        if (! in_array($role, ['admin'])) {
            abort(403);
        }
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);
        $remaining = $expense->remaining();
        $amount = (float) $validated['amount'];
        if ($amount > $remaining + 0.00001) {
            return back()->withErrors(['amount' => 'Amount exceeds remaining balance ('.number_format($remaining,2).')']);
        }

        ExpensePayment::create([
            'expense_id' => $expense->id,
            'amount' => $amount,
            'paid_at' => $validated['paid_at'] ?? now(),
            'paid_by' => Auth::id(),
            'note' => $validated['note'] ?? null,
        ]);

        return back()->with('status', 'Payment recorded');
    }
}
