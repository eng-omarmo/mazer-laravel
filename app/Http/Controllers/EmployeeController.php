<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with(['documents','department'])->latest()->paginate(10);
        $departments = Department::orderBy('name')->get();
        return view('hrm.employees', compact('employees','departments'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('hrm.employees-create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:employees,email'],
            'department_id' => ['nullable','exists:departments,id'],
            'salary' => ['required','numeric','min:0'],
            'bonus' => ['nullable','numeric','min:0'],
            'position' => ['nullable', 'string', 'max:255'],
            'hire_date' => ['nullable', 'date'],
            'cv' => ['required', 'file', 'mimes:pdf,doc,docx'],
            'contract' => ['required', 'file', 'mimes:pdf,doc,docx'],
        ]);

        $employee = Employee::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'department_id' => $validated['department_id'] ?? null,
            'salary' => $validated['salary'],
            'bonus' => $validated['bonus'] ?? null,
            'position' => $validated['position'] ?? null,
            'hire_date' => $validated['hire_date'] ?? null,
            'status' => 'active',
        ]);

        $uploaderId = Auth::id();
        $dir = 'employee_docs/'.$employee->id;

        if ($request->hasFile('cv')) {
            $cvFile = $request->file('cv');
            $cvName = 'cv_'.time().'.'.$cvFile->getClientOriginalExtension();
            $cvPath = Storage::disk('public')->putFileAs($dir, $cvFile, $cvName);
            EmployeeDocument::create([
                'employee_id' => $employee->id,
                'type' => 'cv',
                'path' => $cvPath,
                'status' => 'pending',
                'uploaded_by' => $uploaderId,
            ]);
        }

        if ($request->hasFile('contract')) {
            $ctFile = $request->file('contract');
            $ctName = 'contract_'.time().'.'.$ctFile->getClientOriginalExtension();
            $ctPath = Storage::disk('public')->putFileAs($dir, $ctFile, $ctName);
            EmployeeDocument::create([
                'employee_id' => $employee->id,
                'type' => 'contract',
                'path' => $ctPath,
                'status' => 'pending',
                'uploaded_by' => $uploaderId,
            ]);
        }

        return redirect()->route('hrm.employees.index')->with('status', 'Employee onboarded. Documents pending verification.');
    }
    public function edit(Employee $employee)
    {
        $departments = Department::orderBy('name')->get();
        return view('hrm.employees-edit', compact('employee','departments'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'department_id' => ['nullable','exists:departments,id'],
            'salary' => ['nullable','numeric','min:0'],
            'bonus' => ['nullable','numeric','min:0'],
        ]);
        $employee->update([
            'department_id' => $validated['department_id'] ?? $employee->department_id,
            'salary' => $validated['salary'] ?? $employee->salary,
            'bonus' => $validated['bonus'] ?? $employee->bonus,
        ]);
        return redirect()->route('hrm.employees.index')->with('status', 'Employee updated');
    }

    public function show(Employee $employee)
    {
        $employee->load(['department','documents']);
        return view('hrm.employees-show', compact('employee'));
    }
}