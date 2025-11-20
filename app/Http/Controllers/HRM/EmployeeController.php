<?php

namespace App\Http\Controllers\HRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeRequest;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View
    {
        $employees = Employee::with('department')->orderBy('last_name')->paginate(10);
        return view('hrm.employees.index', compact('employees'));
    }

    public function create(): View
    {
        $departments = Department::orderBy('name')->get();
        return view('hrm.employees.create', compact('departments'));
    }

    public function store(EmployeeRequest $request): RedirectResponse
    {
        Employee::create($request->validated());
        return redirect()->route('hrm.employees.index');
    }

    public function show(Employee $employee): View
    {
        return view('hrm.employees.show', compact('employee'));
    }

    public function edit(Employee $employee): View
    {
        $departments = Department::orderBy('name')->get();
        return view('hrm.employees.edit', compact('employee','departments'));
    }

    public function update(EmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $employee->update($request->validated());
        return redirect()->route('hrm.employees.index');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();
        return redirect()->route('hrm.employees.index');
    }
}