<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpensePayment;
use App\Models\Organization;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\MerchantPaymentService;
use App\Services\MerchantPayService;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['supplier', 'organization']);
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
        $role = strtolower(auth()->user()->role ?? 'hrm');
        // Only Credit Manager or Admin can initiate expense
        if (! in_array($role, ['credit_manager', 'admin'])) {
            abort(403, 'Only Credit Manager can initiate expenses.');
        }

        $validated = $request->validate([
            'type' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'organization_id' => ['nullable', 'exists:organizations,id'],
            // 'status' is not validated from request because it's always pending initially
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
            'status' => 'pending', // Always pending initially
        ]);

        return redirect()->route('hrm.expenses.index')->with('status', 'Expense initiated');
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
            // 'status' => ['required', 'string', 'in:pending,reviewed,approved'], // Status managed via workflow
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
            // 'status' => $validated['status'], // Keep existing status
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
        $role = strtolower(auth()->user()->role ?? 'hrm');
        if (! in_array($role, ['finance', 'admin'])) {
            abort(403, 'Only Finance can review expenses.');
        }

        $expense->update(['status' => 'reviewed']);

        return back()->with('status', 'Expense marked reviewed');
    }

    public function approve(Expense $expense)
    {
        $role = strtolower(auth()->user()->role ?? 'hrm');
        if (! in_array($role, ['admin'])) {
            abort(403, 'Only Admin can approve expenses.');
        }

        $expense->update(['status' => 'approved']);

        return back()->with('status', 'Expense approved');
    }

    public function show(Expense $expense)
    {
        $expense->load(['supplier', 'organization', 'payments', 'payments.expense']);
        $suppliers = Supplier::orderBy('name')->get();
        $organizations = Organization::orderBy('name')->get();

        return view('hrm.expenses-show', compact('expense', 'suppliers', 'organizations'));
    }

    public function pay(Request $request, Expense $expense)
    {
        $role = strtolower(auth()->user()->role ?? 'hrm');
        if (! in_array($role, ['finance', 'admin'])) {
            abort(403, 'Only Finance can initiate payments.');
        }
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);
        $remaining = $expense->remaining();
        $amount = (float) $validated['amount'];
        if ($amount > $remaining) {
            return back()->withErrors(['amount' => 'Amount exceeds remaining balance (' . number_format($remaining, 2) . ')']);
        }

        ExpensePayment::create([
            'expense_id' => $expense->id,
            'amount' => $amount,
            'paid_at' => $validated['paid_at'] ?? now(),
            'paid_by' => Auth::id(),
            'note' => $validated['note'] ?? null,
            'status' => 'pending',
        ]);



        return back()->with('status', 'Payment recorded, pending approval');
    }

    public function pendingExpensePayments()
    {
        $payments = ExpensePayment::where('status', 'pending')->with('expense')->paginate(10);

        return view('hrm.expense-payments-pending', compact('payments'));
    }

    public function approvePayment(ExpensePayment $payment)
    {
        $role = strtolower(auth()->user()->role ?? 'hrm');
        if (! in_array($role, ['admin'])) {
            abort(403, 'Only Admin can approve payments.');
        }
        $payment->update(['status' => 'approved']);
        $payment->expense->updatePaymentStatus();
        $accountInfo =   $this->basedOnPrefixGetPaymentMethod($payment->expense->supplier->account);
        $data = [
            'amount' => $payment->amount,
            'receiver' => $accountInfo['account'],
            'paid_by' => Auth::id(),
            'note' => $payment->note,
            'payment_method' => $accountInfo['payment_method'],
            'status' => 'pending',
        ];

        //merchant payment servce
        $merchantPaymentService = new  MerchantPayService();
        $merchantPaymentService->executeTransaction($data);

        return back()->with('status', 'Payment approved');
    }
    private function basedOnPrefixGetPaymentMethod($account)
    {

        $account = str_replace('+252', '', $account);

        $prefix = substr($account, 0, 2);
        $firstDigit = substr($account, 0, 1);
        if ($prefix === '77' || $prefix === '61') {
            return ['account' => $account, 'payment_method' => 'Hormuud'];
        } elseif ($prefix === '62') {
            return ['account' => $account, 'payment_method' => 'Somtel'];
        } else {
            return ['account' => $account, 'payment_method' => 'Unknown'];
        }
    }


    public function rejectPayment(ExpensePayment $payment)
    {
        $role = strtolower(auth()->user()->role ?? 'hrm');
        if (! in_array($role, ['admin'])) {
            abort(403, 'Only Admin can reject payments.');
        }

        $payment->update(['status' => 'rejected']);
        $payment->expense->updatePaymentStatus();

        return back()->with('status', 'Payment rejected');
    }
}
