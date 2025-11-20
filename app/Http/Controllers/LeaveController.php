<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeLeave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $query = EmployeeLeave::query()->with('employee');
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('type')) {
            $query->where('type', 'like', '%'.$request->string('type').'%');
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
        $employees = Employee::orderBy('first_name')->orderBy('last_name')->get();
        return view('hrm.leave', compact('leaves','employees'));
    }

    public function create()
    {
        $employees = Employee::orderBy('first_name')->orderBy('last_name')->get();
        return view('hrm.leave-create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required','exists:employees,id'],
            'type' => ['required','string','max:100'],
            'start_date' => ['required','date'],
            'end_date' => ['required','date','after_or_equal:start_date'],
            'reason' => ['nullable','string','max:500'],
        ]);

        EmployeeLeave::create($validated);
        return redirect()->route('hrm.leave.index')->with('status', 'Leave request submitted');
    }

    public function approve(EmployeeLeave $leave)
    {
        $leave->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        return back()->with('status', 'Leave approved');
    }

    public function reject(Request $request, EmployeeLeave $leave)
    {
        $request->validate(['reason' => ['nullable','string','max:500']]);
        $leave->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'reason' => $request->input('reason'),
        ]);
        return back()->with('status', 'Leave rejected');
    }

    public function edit(EmployeeLeave $leave)
    {
        $employees = Employee::orderBy('first_name')->orderBy('last_name')->get();
        return view('hrm.leave-edit', compact('leave','employees'));
    }

    public function update(Request $request, EmployeeLeave $leave)
    {
        $validated = $request->validate([
            'employee_id' => ['required','exists:employees,id'],
            'type' => ['required','string','max:100'],
            'start_date' => ['required','date'],
            'end_date' => ['required','date','after_or_equal:start_date'],
            'reason' => ['nullable','string','max:500'],
            'status' => ['required','in:pending,approved,rejected'],
        ]);
        $leave->update($validated);
        return redirect()->route('hrm.leave.index')->with('status', 'Leave updated');
    }

    public function destroy(EmployeeLeave $leave)
    {
        $leave->delete();
        return back()->with('status', 'Leave deleted');
    }
}