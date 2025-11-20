<?php

namespace App\Http\Controllers\HRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $departments = Department::orderBy('name')->paginate(10);
        return view('hrm.departments.index', compact('departments'));
    }

    public function create(): View
    {
        return view('hrm.departments.create');
    }

    public function store(DepartmentRequest $request): RedirectResponse
    {
        Department::create($request->validated());
        return redirect()->route('hrm.departments.index');
    }

    public function show(Department $department): View
    {
        return view('hrm.departments.show', compact('department'));
    }

    public function edit(Department $department): View
    {
        return view('hrm.departments.edit', compact('department'));
    }

    public function update(DepartmentRequest $request, Department $department): RedirectResponse
    {
        $department->update($request->validated());
        return redirect()->route('hrm.departments.index');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $department->delete();
        return redirect()->route('hrm.departments.index');
    }
}