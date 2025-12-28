<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with(['documents', 'department'])->latest()->paginate(10);
        $departments = Department::orderBy('name')->get();

        return view('hrm.employees', compact('employees', 'departments'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        $organizations = \App\Models\Organization::orderBy('name')->get();

        return view('hrm.employees-create', compact('departments', 'organizations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:employees,email'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'salary' => ['required', 'numeric', 'min:0'],
            'bonus' => ['nullable', 'numeric', 'min:0'],
            'reference_full_name' => ['nullable', 'string', 'max:255'],
            'reference_phone' => ['nullable', 'string', 'max:30'],
            'identity_doc_number' => ['nullable', 'string', 'max:100'],
            'fingerprint_id' => ['nullable', 'string', 'max:100', 'unique:employees,fingerprint_id'],
            'position' => ['nullable', 'string', 'max:255'],
            'hire_date' => ['nullable', 'date'],
            'cv' => ['required', 'file', 'mimes:pdf,doc,docx'],
            'contract' => ['required', 'file', 'mimes:pdf,doc,docx'],
            'identity_document' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'account_provider' => ['nullable', 'string', 'in:somtel,hormuud,wallet'],
        ]);

        $employee = Employee::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'department_id' => $validated['department_id'] ?? null,
            'organization_id' => $validated['organization_id'] ?? null,
            'salary' => $validated['salary'],
            'bonus' => $validated['bonus'] ?? null,
            'reference_full_name' => $validated['reference_full_name'] ?? null,
            'reference_phone' => $validated['reference_phone'] ?? null,
            'identity_doc_number' => $validated['identity_doc_number'] ?? null,
            'fingerprint_id' => $validated['fingerprint_id'] ?? null,
            'position' => $validated['position'] ?? null,
            'hire_date' => $validated['hire_date'] ?? null,
            'status' => 'active',
            'account_number' => $validated['account_number'] ?? null,
            'account_provider' => $validated['account_provider'] ?? null,
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

        if ($request->hasFile('identity_document')) {
            $idFile = $request->file('identity_document');
            $idName = 'identity_'.time().'.'.$idFile->getClientOriginalExtension();
            $idPath = Storage::disk('public')->putFileAs($dir, $idFile, $idName);
            EmployeeDocument::create([
                'employee_id' => $employee->id,
                'type' => 'identity',
                'path' => $idPath,
                'status' => 'pending',
                'uploaded_by' => $uploaderId,
            ]);
        }

        return redirect()->route('hrm.employees.index')->with('status', 'Employee onboarded. Documents pending verification.');
    }

    public function edit(Employee $employee)
    {
        $departments = Department::orderBy('name')->get();
        $organizations = \App\Models\Organization::orderBy('name')->get();

        return view('hrm.employees-edit', compact('employee', 'departments', 'organizations'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'department_id' => ['nullable', 'exists:departments,id'],
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'bonus' => ['nullable', 'numeric', 'min:0'],
            'reference_full_name' => ['nullable', 'string', 'max:255'],
            'reference_phone' => ['nullable', 'string', 'max:30'],
            'identity_doc_number' => ['nullable', 'string', 'max:100'],
            'fingerprint_id' => ['nullable', 'string', 'max:100', 'unique:employees,fingerprint_id,'.$employee->id],
            'identity_document' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'account_provider' => ['nullable', 'string', 'in:somtel,hormuud,wallet'],
        ]);
        $employee->update([
            'department_id' => $validated['department_id'] ?? $employee->department_id,
            'organization_id' => $validated['organization_id'] ?? $employee->organization_id,
            'salary' => $validated['salary'] ?? $employee->salary,
            'bonus' => $validated['bonus'] ?? $employee->bonus,
            'reference_full_name' => $validated['reference_full_name'] ?? $employee->reference_full_name,
            'reference_phone' => $validated['reference_phone'] ?? $employee->reference_phone,
            'identity_doc_number' => $validated['identity_doc_number'] ?? $employee->identity_doc_number,
            'fingerprint_id' => $validated['fingerprint_id'] ?? $employee->fingerprint_id,
            'account_number' => $validated['account_number'] ?? $employee->account_number,
            'account_provider' => $validated['account_provider'] ?? $employee->account_provider,
        ]);

        if ($request->hasFile('identity_document')) {
            $uploaderId = Auth::id();
            $dir = 'employee_docs/'.$employee->id;
            $idFile = $request->file('identity_document');
            $idName = 'identity_'.time().'.'.$idFile->getClientOriginalExtension();
            $idPath = Storage::disk('public')->putFileAs($dir, $idFile, $idName);
            EmployeeDocument::create([
                'employee_id' => $employee->id,
                'type' => 'identity',
                'path' => $idPath,
                'status' => 'pending',
                'uploaded_by' => $uploaderId,
            ]);
        }

        return redirect()->route('hrm.employees.index')->with('status', 'Employee updated');
    }

    public function show(Employee $employee)
    {
        $employee->load(['department', 'documents']);

        return view('hrm.employees-show', compact('employee'));
    }
}
