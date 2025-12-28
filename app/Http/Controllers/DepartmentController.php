<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::with(['head'])->orderBy('name')->paginate(10);

        return view('hrm.departments', compact('departments'));
    }

    public function create()
    {
        $employees = Employee::orderBy('first_name')->orderBy('last_name')->get();

        return view('hrm.departments-create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:departments,code'],
            'name' => ['required', 'string', 'max:255'],
            'head_employee_id' => ['nullable', 'exists:employees,id'],
        ]);
        Department::create($validated);

        return redirect()->route('hrm.departments.index')->with('status', 'Department created');
    }

    public function show(Department $department)
    {
        $department->load('head');

        return view('hrm.departments-show', compact('department'));
    }

    public function edit(Department $department)
    {
        $employees = Employee::orderBy('first_name')->orderBy('last_name')->get();

        return view('hrm.departments-edit', compact('department', 'employees'));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:departments,code,'.$department->id],
            'name' => ['required', 'string', 'max:255'],
            'head_employee_id' => ['nullable', 'exists:employees,id'],
        ]);
        $department->update($validated);

        return redirect()->route('hrm.departments.index')->with('status', 'Department updated');
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return back()->with('status', 'Department deleted');
    }
}
